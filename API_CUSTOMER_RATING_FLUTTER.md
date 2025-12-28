# Customer Rating API - Flutter Integration Guide

## 1. Get Customer Rating Information

### Endpoint
```
GET /api/get-customer-rating-info
```

### Authentication
**Required:** Yes (Bearer Token via Sanctum)
- Include `Authorization: Bearer {token}` header
- Token obtained from login API

### Request Parameters

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `customer_id` | integer | Yes | The ID of the customer to get rating information for |

### Request Example (Flutter/Dart)

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

Future<Map<String, dynamic>> getCustomerRatingInfo({
  required String token,
  required int customerId,
}) async {
  final url = Uri.parse('https://frobster.com/api/get-customer-rating-info?customer_id=$customerId');
  
  final response = await http.get(
    url,
    headers: {
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
  );
  
  if (response.statusCode == 200) {
    return json.decode(response.body);
  } else {
    throw Exception('Failed to load customer rating info: ${response.statusCode}');
  }
}
```

### Success Response (200 OK)

```json
{
  "customer_id": 89,
  "customer_name": "Daniela Bayer",
  "average_rating": 5.0,
  "total_reviews": 1,
  "recent_reviews": [
    {
      "id": 1,
      "rating": 5,
      "review": "nice danileaa",
      "provider_name": "ASim Riaz",
      "created_at": "2025-12-28"
    }
  ]
}
```

**Note:** The response data is returned directly (not wrapped in a `data` key).

### Error Responses

**400 Bad Request - Missing customer_id:**
```json
{
  "message": "Customer ID is required"
}
```

**404 Not Found - Customer not found:**
```json
{
  "message": "Customer not found"
}
```

**401 Unauthorized - Invalid/Missing token:**
```json
{
  "message": "Unauthenticated"
}
```

---

## 2. Save Customer Rating (Provider rates Customer)

### Endpoint
```
POST /api/save-customer-rating
```

### Authentication
**Required:** Yes (Bearer Token via Sanctum)
- Provider must be authenticated
- Provider must be the booking's provider

### Request Body

```json
{
  "booking_id": 123,
  "customer_id": 456,
  "rating": 4.5,
  "review": "Great customer! Very punctual and clear communication.",
  "provider_id": 789
}
```

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `booking_id` | integer | Yes | The booking ID for which provider is rating |
| `customer_id` | integer | Yes | The customer/user ID being rated |
| `rating` | double | Yes | Rating value (1.0 to 5.0, can be decimal like 4.5) |
| `review` | string | No | Text review/feedback from provider about customer |
| `provider_id` | integer | No | Provider ID (can be extracted from auth token) |

### Request Example (Flutter/Dart)

```dart
Future<Map<String, dynamic>> saveCustomerRating({
  required String token,
  required int bookingId,
  required int customerId,
  required double rating,
  String? review,
  int? providerId,
}) async {
  final url = Uri.parse('https://frobster.com/api/save-customer-rating');
  
  final body = {
    'booking_id': bookingId,
    'customer_id': customerId,
    'rating': rating,
    if (review != null) 'review': review,
    if (providerId != null) 'provider_id': providerId,
  };
  
  final response = await http.post(
    url,
    headers: {
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
    body: json.encode(body),
  );
  
  if (response.statusCode == 200) {
    return json.decode(response.body);
  } else {
    final error = json.decode(response.body);
    throw Exception(error['message'] ?? 'Failed to save customer rating');
  }
}
```

### Success Response (200 OK)

```json
{
  "message": "Customer rating saved successfully"
}
```

### Error Responses

**400 Bad Request - Validation Error:**
```json
{
  "message": "The rating field is required."
}
```

**403 Forbidden - Unauthorized:**
```json
{
  "message": "Unauthorized action"
}
```

**404 Not Found - Booking not found:**
```json
{
  "message": "Booking not found"
}
```

---

## 3. Complete Flutter Example

### Model Class

```dart
class CustomerRatingInfo {
  final int customerId;
  final String customerName;
  final double averageRating;
  final int totalReviews;
  final List<RecentReview> recentReviews;

  CustomerRatingInfo({
    required this.customerId,
    required this.customerName,
    required this.averageRating,
    required this.totalReviews,
    required this.recentReviews,
  });

  factory CustomerRatingInfo.fromJson(Map<String, dynamic> json) {
    return CustomerRatingInfo(
      customerId: json['customer_id'] ?? 0,
      customerName: json['customer_name'] ?? '',
      averageRating: (json['average_rating'] ?? 0).toDouble(),
      totalReviews: json['total_reviews'] ?? 0,
      recentReviews: (json['recent_reviews'] as List<dynamic>?)
          ?.map((review) => RecentReview.fromJson(review))
          .toList() ?? [],
    );
  }
}

class RecentReview {
  final int id;
  final double rating;
  final String? review;
  final String? providerName;
  final String? createdAt;

  RecentReview({
    required this.id,
    required this.rating,
    this.review,
    this.providerName,
    this.createdAt,
  });

  factory RecentReview.fromJson(Map<String, dynamic> json) {
    return RecentReview(
      id: json['id'] ?? 0,
      rating: (json['rating'] ?? 0).toDouble(),
      review: json['review'],
      providerName: json['provider_name'],
      createdAt: json['created_at'],
    );
  }
}
```

### Service Class

```dart
class CustomerRatingService {
  final String baseUrl = 'https://frobster.com/api';
  final String? authToken;

  CustomerRatingService({this.authToken});

  Map<String, String> get _headers => {
    'Authorization': 'Bearer $authToken',
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };

  // Get Customer Rating Info
  Future<CustomerRatingInfo> getCustomerRatingInfo(int customerId) async {
    final url = Uri.parse('$baseUrl/get-customer-rating-info?customer_id=$customerId');
    
    final response = await http.get(url, headers: _headers);
    
    if (response.statusCode == 200) {
      final jsonData = json.decode(response.body);
      // Response is returned directly, not wrapped in 'data' key
      return CustomerRatingInfo.fromJson(jsonData);
    } else {
      final error = json.decode(response.body);
      throw Exception(error['message'] ?? 'Failed to load customer rating');
    }
  }

  // Save Customer Rating
  Future<String> saveCustomerRating({
    required int bookingId,
    required int customerId,
    required double rating,
    String? review,
  }) async {
    final url = Uri.parse('$baseUrl/save-customer-rating');
    
    final body = {
      'booking_id': bookingId,
      'customer_id': customerId,
      'rating': rating,
      if (review != null && review.isNotEmpty) 'review': review,
    };
    
    final response = await http.post(
      url,
      headers: _headers,
      body: json.encode(body),
    );
    
    if (response.statusCode == 200) {
      final jsonData = json.decode(response.body);
      return jsonData['message'] ?? 'Rating saved successfully';
    } else {
      final error = json.decode(response.body);
      throw Exception(error['message'] ?? 'Failed to save rating');
    }
  }
}
```

### Usage Example

```dart
// Initialize service
final ratingService = CustomerRatingService(
  authToken: 'your_auth_token_here',
);

// Get customer rating info
try {
  final ratingInfo = await ratingService.getCustomerRatingInfo(89);
  
  print('Customer: ${ratingInfo.customerName}');
  print('Average Rating: ${ratingInfo.averageRating}');
  print('Total Reviews: ${ratingInfo.totalReviews}');
  
  // Display stars based on rating
  for (var review in ratingInfo.recentReviews) {
    print('Review: ${review.review}');
    print('Rating: ${review.rating}');
    print('Provider: ${review.providerName}');
  }
} catch (e) {
  print('Error: $e');
}

// Save customer rating
try {
  final message = await ratingService.saveCustomerRating(
    bookingId: 123,
    customerId: 89,
    rating: 4.5,
    review: 'Great customer!',
  );
  
  print('Success: $message');
} catch (e) {
  print('Error: $e');
}
```

---

## Base URL
```
https://frobster.com/api
```

## Authentication
All endpoints require Bearer token authentication:
```
Authorization: Bearer {your_token_here}
```

## Notes
- Rating values must be between 1.0 and 5.0
- Rating can be decimal (e.g., 4.5)
- `provider_id` is optional in save request (extracted from auth token if not provided)
- All dates are in `Y-m-d` format (e.g., "2025-12-28")

