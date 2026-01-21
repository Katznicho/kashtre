# Third-Party API Integration

This document describes the API integration between Kashtre and the Third-Party system.

## Overview

When creating a company client in Kashtre with an insurance company selected, the system automatically creates a corresponding business and user account in the third-party system via API.

## Configuration

### Environment Variables

Add the following to your `.env` file in the Kashtre project:

```env
THIRD_PARTY_API_URL=http://127.0.0.1:8001
THIRD_PARTY_API_TIMEOUT=30
```

**Note:** Update `THIRD_PARTY_API_URL` to match your third-party system's URL (e.g., `http://localhost:8001` or your production URL).

### Third-Party System Setup

The third-party system should be running and accessible at the configured URL. Ensure:

1. Laravel Sanctum is installed and configured
2. API routes are registered
3. Database migrations have been run

## API Endpoints

### Register Business and User

**Endpoint:** `POST /api/v1/businesses/register`

**Request Body:**
```json
{
    "name": "Company Name",
    "code": "optional-code",
    "email": "company@example.com",
    "phone": "+256770123456",
    "address": "Company Address",
    "description": "Optional description",
    "user_name": "Contact Person Name",
    "user_email": "user@example.com",
    "user_username": "username",
    "user_password": "secure-password"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Business and user registered successfully",
    "data": {
        "business": {
            "id": 1,
            "name": "Company Name",
            "code": "COMPANY-CODE",
            "slug": "company-name",
            "email": "company@example.com"
        },
        "user": {
            "id": 1,
            "name": "Contact Person Name",
            "username": "username",
            "email": "user@example.com"
        },
        "access_token": "token-here",
        "token_type": "Bearer"
    }
}
```

## How It Works

1. **Client Creation Flow:**
   - User creates a company client in Kashtre
   - If an insurance company is selected, the system:
     - Generates a username from the company name
     - Generates a secure random password
     - Calls the third-party API to create the business and user
     - Stores the client in Kashtre database

2. **Error Handling:**
   - If the API call fails, the error is logged but client creation continues
   - This ensures Kashtre operations are not blocked by third-party system issues

3. **Credentials:**
   - The generated username and password are displayed to the user after successful registration
   - These credentials can be used to log into the third-party system

## Testing

### Test API Connection

You can test the API connection using curl:

```bash
curl -X POST http://127.0.0.1:8001/api/v1/businesses/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Company",
    "email": "test@example.com",
    "phone": "+256770123456",
    "address": "Test Address",
    "user_name": "Test User",
    "user_email": "user@example.com",
    "user_username": "testuser",
    "user_password": "password123"
  }'
```

## Troubleshooting

### API Connection Issues

1. **Check third-party system is running:**
   ```bash
   curl http://127.0.0.1:8001/api/v1/businesses/register
   ```

2. **Check logs:**
   - Kashtre: `storage/logs/laravel.log`
   - Third-party: `storage/logs/laravel.log`

3. **Verify environment variables:**
   - Ensure `THIRD_PARTY_API_URL` is correct
   - Check firewall/network settings if using different hosts

### Common Issues

- **Timeout errors:** Increase `THIRD_PARTY_API_TIMEOUT` in `.env`
- **Authentication errors:** Ensure Sanctum is properly configured in third-party system
- **Validation errors:** Check that all required fields are provided

## Security Considerations

1. **API Tokens:** The third-party system uses Laravel Sanctum for API authentication
2. **Password Generation:** Passwords are randomly generated and should be changed after first login
3. **HTTPS:** In production, use HTTPS for API communication
4. **Rate Limiting:** Consider implementing rate limiting on API endpoints

## Future Enhancements

- Store third-party business/user IDs in Kashtre for reference
- Implement webhook callbacks for status updates
- Add retry mechanism for failed API calls
- Implement API key authentication between systems
