# Booking reviews – where to see them and what they mean

## Where you see “Review by customer” and “Review by provider”

There are **two different booking pages**. Reviews appear on both, but the URLs are different.

### 1. **Customer-facing booking page (frontend)**

- **URL:** `https://yoursite.com/booking-detail/2`  
  (Replace `2` with the booking ID.)
- **Route name:** `booking.detail`
- **Who uses it:** Logged-in **customers** viewing their own booking.
- **What you see:**
  - **Reviews for this booking** (heading)
  - **Review by customer** – Customer’s review of the provider (from `booking_ratings`).  
    If none: *“No review by customer yet. The customer has not left a review for the provider.”*
  - **Review by provider** – Provider’s review of the customer (from `customer_ratings`).  
    If none: *“No review by provider yet. The provider has not rated the customer for this booking.”*

So for **booking #2**, use:

**`https://frobster.com/booking-detail/2`**  
*(not* `/booking/2` *on the frontend.)*

You must be **logged in as the customer** for that booking. If you see a login page, sign in first.

---

### 2. **Admin panel booking page**

- **URL:** `https://yoursite.com/booking/2`  
  (Same booking ID in the path.)
- **Who uses it:** **Admin / provider / handyman** (dashboard booking view).
- **What you see:**  
  Open the booking, then the **“View status” / Info** tab. You’ll see two cards:
  - **Review by customer** – Customer’s review of the provider.  
    If none: *“No review by customer yet. The customer has not left a review for the provider.”*
  - **Review by provider** – Provider’s review of the customer.  
    If none: *“No review by provider yet. The provider has not rated the customer for this booking.”*

---

## Summary

| Page            | URL                     | Purpose                          |
|-----------------|-------------------------|----------------------------------|
| **Frontend**    | `/booking-detail/2`     | Customer views their booking     |
| **Admin panel** | `/booking/2`            | Admin/provider views booking     |

- **Review by customer** = customer rated the **provider** → stored in **`booking_ratings`**.
- **Review by provider** = provider rated the **customer** → stored in **`customer_ratings`**.

Both sections are **always shown**. If there is no review yet, a short message explains that instead of hiding the section.
