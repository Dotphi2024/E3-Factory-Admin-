# Member Registration & Razorpay Payment Flow Documentation

This document describes the complete flow for member registration, Razorpay fee payment, paid/unpaid status checks, and conditional login handling.

---

## Architecture & Payment Flow

```
┌─────────────────────────┐
│ Member Fills Reg Form   │
│ (First Name, Mobile, etc)│
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│ POST /api/register      │ ──► Creates participant record with:
│                         │     - payment_status: "unpaid"
│                         │     - is_registration_fees_paid: 0
│                         │     - Generates Razorpay Order details directly
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│ Client Opens Razorpay   │ ──► Opens checkout modal with order_id & key_id
│ Checkout Modal / URL    │
└────────────┬────────────┘
             │
             ▼ (Payment Successful)
┌─────────────────────────┐
│ POST /api/razorpay/     │ ──► Verifies HMAC SHA-256 signature
│      verify-payment     │     - Updates payment_status: "paid"
│                         │     - Updates is_registration_fees_paid: 1
│                         │     - Records payment in participant_payments
│                         │     - Generates 1st session entry QR code (if applicable)
│                         │     - Returns logged_in: true & active token
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│ Access Member Dashboard │
└─────────────────────────┘
```

### Conditional Login Behavior (Paid vs Unpaid):
- **When Member Logs In via OTP (`POST /api/verify-otp`):**
  - **If Paid (`payment_status === "paid"`):**
    - `logged_in: true`
    - `redirect_to_payment: false`
    - User proceeds directly into the mobile app / dashboard.
  - **If Unpaid (`payment_status === "unpaid"`):**
    - `logged_in: false`
    - `redirect_to_payment: true`
    - Response includes complete Razorpay order details so the app immediately redirects or opens Razorpay to pay fees.

---

## Base URL
- **Local Dev Server:** `http://localhost:8000` (or `http://127.0.0.1:8000`)
- **API Prefix:** `/api`
- **Full Base Endpoint:** `http://localhost:8000/api`

---

## 1. Member Registration (with Instant Razorpay Order)

Registers a new member into a selected batch, initializes their status as `unpaid`, and returns a ready-to-use Razorpay order object so the frontend can immediately trigger the payment popup.

- **Method:** `POST`
- **URL:** `http://localhost:8000/api/register`
- **Headers:**
  ```http
  Content-Type: application/json
  Accept: application/json
  ```

### Request Parameters (Matching Form Fields)

| Form Field Label | API Parameter Name | Type | Required | Description / Accepted Values | Example |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Select Batch** | `batch_id` | `integer` | **Yes** | ID of the batch (from `batches` table) | `1` |
| **First Name** | `first_name` | `string` | **Yes** | Member's first name | `"Amit"` |
| **Last Name** | `last_name` | `string` | **Yes** | Member's last name | `"Deshmukh"` |
| **Mobile Number** | `mobile` | `string` | **Yes** | Unique 10-digit mobile number | `"9833000001"` |
| **Email** | `email` | `string` | No | Member's email address | `"amit.deshmukh@example.com"` |
| **Birth Date** | `birth_date` | `string` | No | `YYYY-MM-DD` or `DD-MM-YYYY` | `"15-08-1998"` *(or `"1998-08-15"`)* |
| **Address** | `address` | `string` | No | Full street / house address | `"Flat 101, Om Residency, MG Road"` |
| **Country** | `country` | `string` | No | Country name | `"India"` |
| **State** | `state` | `string` | No | State name | `"Maharashtra"` |
| **City** | `city` | `string` | No | City name | `"Nashik"` |
| **Reference By** | `reference_by` *(or `reference`)* | `string` | No | Source of reference | `"Social Media"` *(or `"Member"`, `"Coach"`, `"Head Coach"`, `"Offline Orientation"`, `"Online Orientation"`, `"Advertisement"`)* |
| **Reference Detail** | `reference_detail` | `string` | No | Source detail or referring ID | `"Instagram"` *(or `"Facebook"`, `"Google"`, etc.)* |
| *(Device Token)* | `device_token` | `string` | No | FCM push notification token (mobile only) | `"fM3xk9LP20a:APA91b..."` |

### Example Request Body (JSON)
```json
{
  "batch_id": 1,
  "first_name": "Amit",
  "last_name": "Deshmukh",
  "mobile": "9833000001",
  "email": "amit.deshmukh@example.com",
  "birth_date": "15-08-1998",
  "address": "Flat 101, Om Residency, MG Road",
  "country": "India",
  "state": "Maharashtra",
  "city": "Nashik",
  "reference_by": "Social Media",
  "reference_detail": "Instagram"
}
```

### Success Response (`200 OK`)
```json
{
  "success": true,
  "message": "Registered successfully. Please complete the registration payment.",
  "data": {
    "participant": {
      "id": 889,
      "first_name": "Amit",
      "last_name": "Deshmukh",
      "batch_id": 1,
      "mobile": "9833000001",
      "email": "amit.deshmukh@example.com",
      "birth_date": "1998-08-15",
      "address": "Flat 101, Om Residency, MG Road",
      "city": "Nashik",
      "state": "Maharashtra",
      "country": "India",
      "is_coach": 0,
      "reference_by": "Social Media",
      "reference": "Social Media",
      "reference_detail": "Instagram",
      "profile_photo": "",
      "is_multiple_batch": "no",
      "is_registration_fees_paid": 0,
      "payment_status": "unpaid"
    },
    "token": "49f57d6b38c36b447605d54e4c34a2e858db142a",
    "payment_status": "unpaid",
    "is_registration_fees_paid": 0,
    "logged_in": false,
    "redirect_to_payment": true,
    "payment": {
      "gateway": "razorpay",
      "key_id": "rzp_live_xxxxxxxxxxxxxx",
      "order_id": "order_EKwxwAgItmmXdp",
      "amount": 1920000,
      "amount_in_rupees": 19200,
      "currency": "INR",
      "receipt": "rcpt_reg_886_1792994730",
      "name": "E3 Factory",
      "description": "Registration Fee - Jan 25 nashik",
      "prefill": {
        "name": "Rahul Sharma",
        "contact": "9876543210",
        "email": "rahul.sharma@example.com"
      },
      "fee_breakdown": {
        "registration_fee": 17500,
        "advance_session_fee": 1700,
        "total_amount": 19200
      }
    }
  }
}
```

---

## 2. Create / Re-initiate Razorpay Order

Use this API if the member closed the Razorpay popup earlier or wants to re-initiate payment.

- **Method:** `POST`
- **URL:** `http://localhost:8000/api/razorpay/create-order`  
  *(Alternative authenticated route: `/api/payment/create-order`)*
- **Headers:**
  ```http
  Content-Type: application/json
  Accept: application/json
  Authorization: Bearer <token> (Optional if mobile or participant_id is sent in body)
  ```

### Request Body (JSON)
```json
{
  "mobile": "9876543210"
}
```
*or by participant ID:*
```json
{
  "participant_id": 886
}
```

### Success Response (`200 OK`)
```json
{
  "success": true,
  "message": "Razorpay order created successfully. Proceed to payment.",
  "data": {
    "payment_status": "unpaid",
    "is_registration_fees_paid": 0,
    "logged_in": false,
    "redirect_to_payment": true,
    "gateway": "razorpay",
    "key_id": "rzp_live_xxxxxxxxxxxxxx",
    "order_id": "order_EKwxwAgItmmXdp",
    "amount": 1920000,
    "amount_in_rupees": 19200,
    "currency": "INR",
    "receipt": "rcpt_reg_886_1792994806",
    "name": "E3 Factory",
    "description": "Registration Fee - Jan 25 nashik",
    "prefill": {
      "name": "Rahul Sharma",
      "contact": "9876543210",
      "email": "rahul.sharma@example.com"
    },
    "fee_breakdown": {
      "registration_fee": 17500,
      "advance_session_fee": 1700,
      "total_amount": 19200
    },
    "token": "49f57d6b38c36b447605d54e4c34a2e858db142a"
  }
}
```

*If already paid:*
```json
{
  "success": false,
  "message": "Registration fees are already paid.",
  "data": {
    "payment_status": "paid",
    "is_registration_fees_paid": 1,
    "logged_in": true,
    "redirect_to_payment": false
  }
}
```

---

## 3. Verify Razorpay Payment (Activate & Log In)

Once the member successfully pays on Razorpay, Razorpay returns `razorpay_order_id`, `razorpay_payment_id`, and `razorpay_signature`. Send these to this endpoint to verify and mark the participant as `paid`.

- **Method:** `POST`
- **URL:** `http://localhost:8000/api/razorpay/verify-payment`  
  *(Alternative authenticated route: `/api/payment/verify`)*
- **Headers:**
  ```http
  Content-Type: application/json
  Accept: application/json
  Authorization: Bearer <token> (Optional if mobile or participant_id is provided)
  ```

### Request Parameters

| Parameter | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `razorpay_order_id` | `string` | **Yes** | Order ID returned by Razorpay Checkout |
| `razorpay_payment_id` | `string` | **Yes** | Payment ID returned by Razorpay Checkout |
| `razorpay_signature` | `string` | **Yes** | Cryptographic HMAC signature from Razorpay |
| `mobile` | `string` | No* | Participant mobile number (*required if no token sent) |
| `participant_id` | `integer`| No* | Participant ID (*required if no token sent) |

### Example Request Body
```json
{
  "mobile": "9876543210",
  "razorpay_order_id": "order_EKwxwAgItmmXdp",
  "razorpay_payment_id": "pay_EKwyv8wKjLz7ab",
  "razorpay_signature": "9ef47621c43167198a0...sha256_hash_here..."
}
```

### Success Response (`200 OK`)
```json
{
  "success": true,
  "message": "Payment verified successfully. Registration completed and logged in.",
  "data": {
    "logged_in": true,
    "payment_status": "paid",
    "is_registration_fees_paid": 1,
    "redirect_to_payment": false,
    "token": "49f57d6b38c36b447605d54e4c34a2e858db142a",
    "participant": {
      "id": 886,
      "first_name": "Rahul",
      "last_name": "Sharma",
      "batch_id": 1,
      "mobile": "9876543210",
      "email": "rahul.sharma@example.com",
      "payment_status": "paid",
      "is_registration_fees_paid": 1
    }
  }
}
```

---

## 4. Check Paid or Unpaid Status API

Allows the mobile app / frontend to check whether a member has paid fees or is unpaid. If unpaid, it automatically provides Razorpay checkout parameters so the user can be redirected to payment.

- **Method:** `GET` or `POST`
- **URL:** `http://localhost:8000/api/payment-status`  
  *(Alternative route: `/api/payment/status`)*
- **Headers:**
  ```http
  Accept: application/json
  Authorization: Bearer <token> (Optional if mobile or participant_id is provided)
  ```

### Query / Body Parameters:
- `?mobile=9876543210` OR
- `?participant_id=886` OR
- Via `Authorization: Bearer <token>`

### Response When Member is PAID:
```json
{
  "success": true,
  "message": "Participant is paid and authorized.",
  "data": {
    "participant_id": 886,
    "first_name": "Rahul",
    "last_name": "Sharma",
    "mobile": "9876543210",
    "batch_id": 1,
    "batch_name": "Jan 25 nashik",
    "payment_status": "paid",
    "is_registration_fees_paid": 1,
    "logged_in": true,
    "redirect_to_payment": false,
    "amount_payable": 0,
    "token": "49f57d6b38c36b447605d54e4c34a2e858db142a",
    "payment": null
  }
}
```

### Response When Member is UNPAID:
```json
{
  "success": true,
  "message": "Participant is unpaid. Redirect to Razorpay to complete registration payment.",
  "data": {
    "participant_id": 886,
    "first_name": "Rahul",
    "last_name": "Sharma",
    "mobile": "9876543210",
    "batch_id": 1,
    "batch_name": "Jan 25 nashik",
    "payment_status": "unpaid",
    "is_registration_fees_paid": 0,
    "logged_in": false,
    "redirect_to_payment": true,
    "amount_payable": 19200,
    "token": "49f57d6b38c36b447605d54e4c34a2e858db142a",
    "payment": {
      "gateway": "razorpay",
      "key_id": "rzp_live_xxxxxxxxxxxxxx",
      "order_id": "order_EKwxwAgItmmXdp",
      "amount": 1920000,
      "amount_in_rupees": 19200,
      "currency": "INR",
      "name": "E3 Factory",
      "description": "Registration Fee - Jan 25 nashik",
      "prefill": {
        "name": "Rahul Sharma",
        "contact": "9876543210",
        "email": "rahul.sharma@example.com"
      },
      "fee_breakdown": {
        "registration_fee": 17500,
        "advance_session_fee": 1700,
        "total_amount": 19200
      }
    }
  }
}
```

---

## 5. Login & Verify OTP (Conditional Paid/Unpaid Handling)

### A. Send Login OTP
- **Method:** `POST`
- **URL:** `http://localhost:8000/api/login`
- **Body:**
  ```json
  {
    "mobile": "9876543210",
    "device_token": "fcm_token_here"
  }
  ```
- **Response includes `payment_status`:**
  ```json
  {
    "success": true,
    "message": "OTP sent successfully",
    "data": {
      "otp": 1234,
      "expiry": "2026-10-26T12:00:00.000000Z",
      "payment_status": "paid",
      "is_registration_fees_paid": 1
    }
  }
  ```

### B. Verify Login OTP
- **Method:** `POST`
- **URL:** `http://localhost:8000/api/verify-otp`
- **Body:**
  ```json
  {
    "mobile": "9876543210",
    "otp": 1234
  }
  ```

#### Case 1: Member is Paid (`payment_status === "paid"`)
User is logged in and allowed into the application.
```json
{
  "success": true,
  "message": "OTP verified successfully",
  "data": {
    "participant_id": 886,
    "is_coach": 0,
    "token": "49f57d6b38c36b447605d54e4c34a2e858db142a",
    "payment_status": "paid",
    "is_registration_fees_paid": 1,
    "logged_in": true,
    "redirect_to_payment": false,
    "is_multiple_batch": "no"
  }
}
```

#### Case 2: Member is Unpaid (`payment_status === "unpaid"`)
User is NOT logged in and client receives Razorpay payment details to redirect to Razorpay.
```json
{
  "success": true,
  "message": "OTP verified. Registration fee payment is pending. Please complete payment to access your account.",
  "data": {
    "participant_id": 886,
    "is_coach": 0,
    "token": "49f57d6b38c36b447605d54e4c34a2e858db142a",
    "payment_status": "unpaid",
    "is_registration_fees_paid": 0,
    "logged_in": false,
    "redirect_to_payment": true,
    "is_multiple_batch": "no",
    "payment": {
      "gateway": "razorpay",
      "key_id": "rzp_live_xxxxxxxxxxxxxx",
      "order_id": "order_EKwxwAgItmmXdp",
      "amount": 1920000,
      "amount_in_rupees": 19200,
      "currency": "INR",
      "name": "E3 Factory",
      "description": "Registration Fee - Jan 25 nashik",
      "prefill": {
        "name": "Rahul Sharma",
        "contact": "9876543210",
        "email": "rahul.sharma@example.com"
      },
      "fee_breakdown": {
        "registration_fee": 17500,
        "advance_session_fee": 1700,
        "total_amount": 19200
      }
    }
  }
}
```

---

## 6. Frontend / Mobile App Razorpay Checkout Integration Example

### In JavaScript / Web / React:
```javascript
// 1. Submit Registration Form
const regResponse = await fetch('http://localhost:8000/api/register', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify(formData)
});
const result = await regResponse.json();

if (result.success && result.data.redirect_to_payment) {
  const paymentData = result.data.payment;

  // 2. Open Razorpay Popup
  const options = {
    key: paymentData.key_id,
    amount: paymentData.amount, // in paise
    currency: paymentData.currency,
    name: paymentData.name,
    description: paymentData.description,
    order_id: paymentData.order_id,
    prefill: paymentData.prefill,
    handler: async function (response) {
      // 3. Send payment proof to verify-payment endpoint
      const verifyRes = await fetch('http://localhost:8000/api/razorpay/verify-payment', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${result.data.token}`
        },
        body: JSON.stringify({
          participant_id: result.data.participant.id,
          razorpay_order_id: response.razorpay_order_id,
          razorpay_payment_id: response.razorpay_payment_id,
          razorpay_signature: response.razorpay_signature
        })
      });

      const verifyData = await verifyRes.json();
      if (verifyData.success && verifyData.data.logged_in) {
        // Save token and navigate to logged-in dashboard!
        localStorage.setItem('auth_token', verifyData.data.token);
        window.location.href = '/dashboard';
      }
    },
    modal: {
      ondismiss: function() {
        alert('Payment pending. You can complete it anytime by logging in.');
      }
    }
  };

  const rzp = new Razorpay(options);
  rzp.open();
}
```

---

## 7. Environment Configuration

Add the Razorpay credentials to your `.env` file:

```env
RAZORPAY_KEY=rzp_live_your_key_id_here
RAZORPAY_SECRET=your_key_secret_here
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret_here
```
*(When no keys are specified in `.env`, the system automatically functions in safe sandbox/mock mode for development testing).*
