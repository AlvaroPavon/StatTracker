# Add project specific ProGuard rules here.
# By default, the flags in this file are appended to flags specified
# in /sdk/tools/proguard/proguard-android.txt

# Keep data classes for Gson
-keep class com.stattracker.mobile.data.model.** { *; }
-keepclassmembers class com.stattracker.mobile.data.model.** { *; }
