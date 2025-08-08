# Bank and Branch API Configuration

This document explains how to configure the external API endpoints for bank and branch lookups in the Customer Management System.

## Environment Variables

Add the following variables to your `.env` file:

```env
# Bank API Configuration
BANK_API_URL=https://your-bank-api-endpoint.com/banks
BANK_API_TIMEOUT=10
BANK_API_KEY=your_bank_api_key_here

# Branch API Configuration
BRANCH_API_URL=https://your-branch-api-endpoint.com/branches
BRANCH_API_TIMEOUT=10
BRANCH_API_KEY=your_branch_api_key_here
```

## API Endpoint Requirements

### Bank API Endpoint

**URL**: `GET /banks`

**Query Parameters**:
- `bank_code` (required): 4-digit bank code
- `country` (optional): Country code (default: 'GH' for Ghana)

**Expected Response Format**:
```json
{
    "bank_name": "Ghana Commercial Bank",
    "success": true
}
```

**Alternative Response Formats**:
```json
{
    "name": "Ghana Commercial Bank"
}
```

```json
{
    "data": {
        "bank_name": "Ghana Commercial Bank"
    }
}
```

### Branch API Endpoint

**URL**: `GET /branches`

**Query Parameters**:
- `branch_code` (required): 3-digit branch code
- `country` (optional): Country code (default: 'GH' for Ghana)

**Expected Response Format**:
```json
{
    "branch_name": "Accra Main Branch",
    "success": true
}
```

**Alternative Response Formats**:
```json
{
    "name": "Accra Main Branch"
}
```

```json
{
    "data": {
        "branch_name": "Accra Main Branch"
    }
}
```

## Authentication

If your API requires authentication, set the `BANK_API_KEY` and/or `BRANCH_API_KEY` environment variables. The system will automatically include the API key in the Authorization header as a Bearer token.

## Fallback Mechanism

The system includes a robust fallback mechanism:

1. **Primary**: External API call
2. **Fallback**: Local hardcoded data if API fails
3. **Error Handling**: Comprehensive logging and error reporting

## Testing the API

You can test the API endpoints using the following routes:

- Bank API: `GET /api/bank-name?bank_code=0001`
- Branch API: `GET /api/branch-name?branch_code=001`

## Logging

All API calls are logged with the following information:
- Request parameters
- Response status
- Error messages (if any)
- Fallback usage

Check the Laravel logs (`storage/logs/laravel.log`) for detailed API interaction logs.

## Example API Implementation

If you need to implement your own API endpoint, here's an example structure:

```php
// Example API Controller
public function getBankName(Request $request)
{
    $bankCode = $request->get('bank_code');
    
    $banks = [
        '0001' => 'Ghana Commercial Bank',
        '0002' => 'Standard Chartered Bank',
        '0003' => 'Barclays Bank Ghana',
        '0004' => 'Ecobank Ghana',
        '0005' => 'Zenith Bank Ghana',
    ];
    
    return response()->json([
        'bank_name' => $banks[$bankCode] ?? null,
        'success' => isset($banks[$bankCode])
    ]);
}
```

## Troubleshooting

### Common Issues

1. **API Timeout**: Increase `BANK_API_TIMEOUT` or `BRANCH_API_TIMEOUT`
2. **Authentication Errors**: Verify API keys are correct
3. **Response Format**: Ensure your API returns data in the expected format
4. **CORS Issues**: Configure your API to allow requests from your application domain

### Debug Mode

Enable debug mode in your `.env` file to see detailed error messages:

```env
APP_DEBUG=true
```

### Monitoring

Monitor API performance and errors through Laravel's logging system. Consider implementing additional monitoring for production environments.


