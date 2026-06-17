import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:path_provider/path_provider.dart';
import 'package:http/http.dart' as http;
import 'package:provider/provider.dart';
import 'package:responsive_sizer/responsive_sizer.dart';

import '../../main.dart';
import '../../screens/creation/image/image_category_screen.dart';
import '../../screens/creation/video/video_category_screen.dart';
import '../../utils/color_util.dart';
import '../../utils/common_util.dart';
import '../../utils/image_util.dart';
import '../../utils/string_util.dart';
import '../../utils/widget_util.dart';
import '../../viewmodel/image/image_category_viewmodel.dart';
import '../../viewmodel/image/image_sub_category_viewmodel.dart';
import '../../viewmodel/video/video_category_viewmodel.dart';
import '../../viewmodel/video/video_sub_category_viewmodel.dart';
import '../slide_page_route.dart';
// Import the new handlers
import 'app_state/app_background.dart';
import 'app_state/app_foreground.dart';
import 'app_state/app_killed.dart';

class FirebaseNotificationService {
  final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  final FlutterLocalNotificationsPlugin _localNotifications =
  FlutterLocalNotificationsPlugin();

  /// Initialize Firebase Push Notifications
  Future<void> init(BuildContext context) async {
    await _requestPermission();
    await _initLocalNotifications();

    // ✅ iOS: Wait for APNS token
    if (Platform.isIOS) {
      String? apnsToken = await _messaging.getAPNSToken();
      // CommonUtil.printLog("🍎 APNS Token: $apnsToken");
    }

    /// ✅ Get and print the initial FCM token
    String? token = await _messaging.getToken();
    if (token != null) {
      CommonUtil.printLog("🔥 FCM Token: $token");
    } else {
      // CommonUtil.printLog("⚠️ FCM Token is null (try again later)");
    }

    _messaging.onTokenRefresh.listen((newToken) {
      // CommonUtil.printLog("🔄 FCM Token refreshed: $newToken");
    });

    try {
      await _messaging.subscribeToTopic('com.aiimage.aiphotoeditor');
      // CommonUtil.printLog("✅ Subscribed to topic: com.aiphotoprompts.app");
    } catch (e) {
      // CommonUtil.printLog("⚠️ subscribeToTopic failed: $e");
    }

    // --- State Handlers ---

    // Foreground message → show local notification
    FirebaseMessaging.onMessage.listen((RemoteMessage message) async {
      handleForegroundMessage(message, this);
    });

    // Background / notification tap
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      handleMessageOpenedApp(message, this);
    });

    // App launched from terminated state
    handleInitialMessage(this);
  }

  // Exposed method for app_killed.dart to get initial message
  Future<RemoteMessage?> getInitialMessage() async {
    return await _messaging.getInitialMessage();
  }

  Future<void> _requestPermission() async {
    NotificationSettings settings = await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );
  }

  Future<void> _initLocalNotifications() async {
    const AndroidInitializationSettings androidSettings =
    AndroidInitializationSettings('@mipmap/ic_launcher');

    final DarwinInitializationSettings iosSettings =
    DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );

    final InitializationSettings initSettings = InitializationSettings(
      android: androidSettings,
      iOS: iosSettings,
    );

    await _localNotifications.initialize(
      initSettings,
      onDidReceiveNotificationResponse: (details) {
        final payload = details.payload;
        if (payload != null && payload.isNotEmpty) {
          final parts = payload.split('|');
          if (parts.length >= 1) {
            final id = parts[0];
            final type = parts.length > 1 ? parts[1] : "img";
            final imageUrl = parts.length > 2 ? parts[2] : null;
            showOpenCategoryDialog(
              categoryId: id,
              type: type,
              imageUrl: imageUrl,
            );
          }
        }
      },
    );

    // Create notification channel for Android
    const AndroidNotificationChannel channel = AndroidNotificationChannel(
      'trendy_ai_channel',
      'Trendy AI Notifications',
      description: StringUtil.notificationForTrendyAIApp,
      importance: Importance.high,
      playSound: true,
      enableVibration: true,
    );

    await _localNotifications
        .resolvePlatformSpecificImplementation<
        AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(channel);
  }

  /// Download home from URL and save to temp file
  Future<String?> _downloadImage(String url, String fileName) async {
    try {
      final response = await http
          .get(Uri.parse(url), headers: {'User-Agent': 'Mozilla/5.0'})
          .timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final bytes = response.bodyBytes;

        if (bytes.length < 100) {
          return null;
        }

        final tempDir = await getTemporaryDirectory();
        final file = File('${tempDir.path}/$fileName');
        await file.writeAsBytes(bytes);

        if (await file.exists()) {
          return file.path;
        } else {
          return null;
        }
      } else {}
    } catch (e, stackTrace) {}
    return null;
  }

  // Exposed method for app_foreground.dart
  Future<void> showLocalNotification(RemoteMessage message) async {
    try {
      final notification = message.notification;
      final data = message.data;

      if (notification == null) {
        return;
      }

      String? imagePath;

      // Try multiple keys for home URL
      final imageUrl =
          data['image_url']?.toString() ??
              data['home']?.toString() ??
              notification.android?.imageUrl ??
              notification.apple?.imageUrl;

      if (imageUrl != null && imageUrl.isNotEmpty) {
        final timestamp = DateTime.now().millisecondsSinceEpoch;
        imagePath = await _downloadImage(
          imageUrl,
          'notif_image_$timestamp.jpg',
        );

        if (imagePath != null) {
        } else {}
      } else {}

      AndroidNotificationDetails androidDetails;
      if (imagePath != null && await File(imagePath).exists()) {
        androidDetails = AndroidNotificationDetails(
          'trendy_ai_channel',
          StringUtil.TrendyAINotifications,
          channelDescription: StringUtil.notificationForTrendyAIApp,
          importance: Importance.high,
          priority: Priority.high,
          playSound: true,
          enableVibration: true,
          styleInformation: BigPictureStyleInformation(
            FilePathAndroidBitmap(imagePath),
            contentTitle: notification.title ?? 'Notification',
            summaryText: notification.body ?? '',
            largeIcon: FilePathAndroidBitmap(imagePath),
          ),
          icon: '@mipmap/ic_launcher',
        );
      } else {
        androidDetails = const AndroidNotificationDetails(
          'trendy_ai_channel', // must match the channel created in _initLocalNotifications()
          StringUtil.TrendyAINotifications,
          channelDescription: StringUtil.notificationForTrendyAIApp,
          importance: Importance.high,
          priority: Priority.high,
          playSound: true,
          enableVibration: true,
          icon: '@mipmap/ic_launcher',
        );
      }

      DarwinNotificationAttachment? iosAttachment;
      if (imagePath != null && await File(imagePath).exists()) {
        try {
          iosAttachment = DarwinNotificationAttachment(imagePath);
        } catch (e) {}
      }

      final platformDetails = NotificationDetails(
        android: androidDetails,
        iOS: DarwinNotificationDetails(
          attachments: iosAttachment != null ? [iosAttachment] : null,
          presentAlert: true,
          presentBadge: true,
          presentSound: true,
        ),
      );

      final categoryId = data['category_id']?.toString() ?? '';
      final type = data['type']?.toString() ?? 'img';
      final payload = '$categoryId|$type|${imageUrl ?? ''}';

      await _localNotifications.show(
        notification.hashCode,
        notification.title ?? 'Notification',
        notification.body ?? '',
        platformDetails,
        payload: payload,
      );
    } catch (e, stackTrace) {}
  }

  Future<void> showOpenCategoryDialog({required String categoryId, String? categoryName, String type = "img", String? imageUrl}) async {
    final context = navigatorKey.currentContext;
    if (context == null) return;

    String finalCategoryName = categoryName ?? "";

    if (finalCategoryName.isEmpty) {
      if (type == "vid") {
        final categoryProvider = context.read<VideoCategoryViewModel>();
        finalCategoryName = await categoryProvider.getCategoryNameById(categoryId);
      } else {
        final categoryProvider = context.read<ImageCategoryViewModel>();
        finalCategoryName = await categoryProvider.getCategoryNameById(categoryId);
      }
    }

    if (finalCategoryName.isEmpty) return; // Still empty, maybe ID was invalid

    final dialogMessage = "${StringUtil.exploreCategoryPart1}$finalCategoryName${StringUtil.exploreCategoryPart2}";

    _showNotificationImageDialog(
      context: context,
      title: type == "vid" ? StringUtil.newVideosAreHere : StringUtil.newImagesAreHere,
      message: dialogMessage,
      imageUrl: imageUrl,
      primaryButtonText: StringUtil.strContinue,
      cancelButtonText: StringUtil.notNow,
      onPrimaryTap: () async {
        Navigator.pop(context); // close dialog
        _navigateToCategoryScreen(
          categoryId: categoryId,
          categoryName: finalCategoryName,
          type: type,
        );
      },
    );
  }

  void _showNotificationImageDialog({
    required BuildContext context,
    required String title,
    required String message,
    required String? imageUrl,
    required String primaryButtonText,
    required VoidCallback onPrimaryTap,
    String cancelButtonText = StringUtil.notNow,
    VoidCallback? onCancelTap,
  }) {
    WidgetUtil.showCustomDialog(
      context: context,
      title: "",
      message: "",
      showAppIcon: false,
      primaryButtonText: primaryButtonText,
      onPrimaryTap: onPrimaryTap,
      cancelButtonText: cancelButtonText,
      onCancelTap: onCancelTap,
      customChild: Column(
        children: [
          if (imageUrl != null && imageUrl.isNotEmpty)
            Padding(
              padding: EdgeInsets.only(bottom: 2.h),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(2.h),
                child: Stack(
                  children: [
                    Image.network(
                      imageUrl,
                      width: double.infinity,
                      fit: BoxFit.fitWidth,
                      errorBuilder: (context, error, stackTrace) => Container(
                        height: 20.h,
                        width: double.infinity,
                        color: ColorUtil.grey800,
                        child: Center(
                          child: Icon(Icons.image_not_supported, color: ColorUtil.white, size: 30.sp),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          WidgetUtil.poppins(
            title,
            fontSize: 20.sp,
            fontWeight: FontWeight.w700,
            color: ColorUtil.white,
            textAlign: TextAlign.center,
          ),
          SizedBox(height: 1.h),
          WidgetUtil.poppins(
            message,
            textAlign: TextAlign.center,
            fontSize: 16.sp,
            fontWeight: FontWeight.w500,
            color: ColorUtil.white70,
            maxLines: 6,
          ),
        ],
      ),
    );
  }

  void _navigateToCategoryScreen({
    required String categoryId,
    required String categoryName,
    String type = "img",
  }) async {
    final context = navigatorKey.currentContext;
    if (context == null) {
      await Future.delayed(const Duration(milliseconds: 300));
      return _navigateToCategoryScreen(
        categoryId: categoryId,
        categoryName: categoryName,
        type: type,
      );
    }

    try {
      if (type == "vid") {
        navigatorKey.currentState?.push(
          SlidePageRoute(
            page: VideoCategoryScreen(
              categoryId: categoryId,
              categoryName: categoryName,
            ),
          ),
        );
      } else {
        navigatorKey.currentState?.push(
          SlidePageRoute(
            page: CategoryScreen(
              categoryId: categoryId,
              categoryName: categoryName,
            ),
          ),
        );
      }
    } catch (e) {
      // Handle navigation error
    }
  }
}
