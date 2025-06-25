<?php

namespace   App\Enums;

enum ActionType: string
{
    case CREATE = "can_create";
    case UPDATE = "can_update";
    case READ = "can_read";
    case DELETE = "can_delete";
}

enum ApprovalStatus: string
{
    case PENDING = "Pending";
    case ACCEPTED = "Accepted";
    case REJECTED = "Rejected";
}

enum BookingStatus: string
{
    case PENDING = "Pending";
    case ACCEPTED = "Accepted";
    case REJECTED = "Rejected";
    case ASSIGNED = "Assigned";
    case RE_ASSIGNED = "Re_Assigned";
    case SERIVCE_STARTED = "Service_Started";
    case SERIVCE_COMPLETED = "Service_Completed";
}

enum CmsCode: string
{
    case ABOUT_US = "about_us";
    case PRIVACY_POLICY = "privacy_policy";
}

enum Days: string
{
    case Monday = 'Monday';
    case Tuesday = 'Tuesday';
    case Wednesday = 'Wednesday';
    case Thursday = 'Thursday';
    case Friday = 'Friday';
    case Saturday = 'Saturday';
    case Sunday = 'Sunday';
}

enum NotificationPushType: string
{
    case FCM = "FCM";
    case WEB_PUSH =  "WEB_PUSH";
}

enum NotificationType: string
{
    case ADMIN = "admin";
    case BOOKING = "booking";
    case BATTERY_WARRANTY_REQ = "battery-warranty-request";
    case INSURANCE_LEAD_REQ = "insurance-lead-request";
    case TECH_REASSIGNED = "technician-reassigned";
    case TECH_ASSIGNED = "technician-assigned";
    case SUBSCRIPTION = "subscription";
    case PAYMENT = "payment";
}

enum NotifyToTypes: string
{
    case ALL = "All";
    case SEPECIFIC_USER = "Specific";
}

enum NotifyUserType: string
{
    case ALL = "All";
    case USER = "User";
    case VENDOR = "Vendor";
    case TECHNICIAN = "Technician";
}

enum OTPFor: string
{
    case LOGIN = "Login";
    case FORGOT_PASSWORD = "Forgot_password";
}

enum OTPStatus: string
{
    case NOT_VALIDATED = "Not_validated";
    case VALIDATED = "Validated";
}
enum PaymentStatus: string
{
    case PENDING = "Pending";
    case SUCCESS = "Success";
    case FAILED = "Failed";
    case CANCELLED = "Cancelled";
    case REFUND_INPROGRESS = "Refund_Inprogress";
    case REFUND_INITIATED = "Refund_Initiated";
    case REFUND_SUCCESS = "Refund_Success";
    case REFUND_FAILED = "Refund_Failed";
}

enum RoleCodes: string
{
    case CODE_DASHBOARD = "dashboard";
    case CODE_USER = "users";
    case CODE_CUSTOMER = "customers";
    case CODE_VENDOR = "vendors";
    case CODE_CMS = "cms";
    case CODE_NOTIFICATION = "notifications";
    case CODE_SUBSCRIPTION = "subscriptions";
    case CODE_ROLES = "roles";
    case CODE_SETTING = "settings";
    case CODE_SUPPORT = "supports";
}

enum Status: string
{
    case ACTIVE = "Active";
    case INACTIVE = "In_Active";
}

enum SubscriptionPlanType: string
{
    case MONTHLY = "Monthly";
    case QUARTERLY = "Quarterly";
    case YEARLY = "Yearly";
}

enum SubscriptionTypes: string
{
    case ON_ROAD_SERVICE = "on_road_service";
    case BATTERY_HEALTH_MONITORING = "battery_health_monitoring";
}

enum TransactionType: string
{
    case CHARGE = "Charge";
    case REFUND = "Refund";
}

enum UserSubscriptionStatus: string
{
    case PENDING = "Pending";      // Awaiting payment or in trial
    case ACTIVE = "Active";        // Payment successful, subscription is active
    case INACTIVE = "In_Active";        // Payment successful, subscription is active
    case EXPIRED = "Expired";      // Subscription ended due to time lapse
    case REJECTED = "Rejected";    // Payment failed, never activated
    case CANCELED = "Canceled";    // User or admin canceled before expiry
}

enum UserTypes: string
{
    case USER = "User";
    case VENDOR = "Vendor";
    case TECHNICIAN = "Technician";
}

enum VerificationStatus: string
{
    case PENDING = "Pending";
    case ACCEPTED = "Accepted";
    case REJECTED = "Rejected";
}
