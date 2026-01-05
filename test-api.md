# Testing dashboard-detail API

## 1. Basic Request (No Parameters)
```bash
curl -X GET "http://127.0.0.1:8000/api/dashboard-detail"
```

## 2. With City Filter
```bash
curl -X GET "http://127.0.0.1:8000/api/dashboard-detail?city_id=1"
```

## 3. With Location (Latitude/Longitude)
```bash
curl -X GET "http://127.0.0.1:8000/api/dashboard-detail?latitude=40.7128&longitude=-74.0060"
```

## 4. With Customer ID (to get reviews, notifications, upcoming booking)
```bash
curl -X GET "http://127.0.0.1:8000/api/dashboard-detail?customer_id=45"
```

## 5. Combined Parameters
```bash
curl -X GET "http://127.0.0.1:8000/api/dashboard-detail?city_id=1&customer_id=45&latitude=40.7128&longitude=-74.0060"
```

## 6. Pretty Print JSON Response
```bash
curl -X GET "http://127.0.0.1:8000/api/dashboard-detail" | json_pp
```

## 7. Save Response to File
```bash
curl -X GET "http://127.0.0.1:8000/api/dashboard-detail" -o response.json
```

## 8. With Headers (if needed)
```bash
curl -X GET "http://127.0.0.1:8000/api/dashboard-detail" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json"
```

