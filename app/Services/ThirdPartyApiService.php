<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ThirdPartyApiService
{
    protected $baseUrl;
    protected $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.third_party.api_url', env('THIRD_PARTY_API_URL', 'http://127.0.0.1:8001'));
        $this->timeout = config('services.third_party.timeout', 30);
    }

    /**
     * Register a business and user in the third-party system
     *
     * @param array $businessData
     * @param array $userData
     * @param int|null $connectToInsuranceCompanyId Optional: ID of insurance company to connect to
     * @return array|null
     */
    public function registerBusinessAndUser(array $businessData, array $userData, ?int $connectToInsuranceCompanyId = null): ?array
    {
        try {
            $payload = [
                // Business/Insurance Company data
                'name' => $businessData['name'] ?? '',
                'code' => $businessData['code'] ?? null,
                'email' => $businessData['email'] ?? '',
                'phone' => $businessData['phone'] ?? null,
                'address' => $businessData['address'] ?? null,
                'head_office_address' => $businessData['head_office_address'] ?? $businessData['address'] ?? null,
                'postal_address' => $businessData['postal_address'] ?? null,
                'website' => $businessData['website'] ?? null,
                'description' => $businessData['description'] ?? null,
                
                // User data
                'user_name' => $userData['name'] ?? '',
                'user_email' => $userData['email'] ?? '',
                'user_username' => $userData['username'] ?? '',
                'user_password' => $userData['password'] ?? '',
            ];
            
            // Add connection if provided
            if ($connectToInsuranceCompanyId) {
                $payload['connect_to_insurance_company_id'] = $connectToInsuranceCompanyId;
            }

            Log::info('ThirdPartyApiService: Registering business and user', [
                'business_name' => $payload['name'],
                'user_email' => $payload['user_email'],
            ]);

            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/v1/businesses/register", $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('ThirdPartyApiService: Business and user registered successfully', [
                    'business_id' => $data['data']['business']['id'] ?? null,
                    'user_id' => $data['data']['user']['id'] ?? null,
                ]);

                return $data['data'] ?? null;
            } else {
                $error = $response->json();
                Log::error('ThirdPartyApiService: Failed to register business and user', [
                    'status' => $response->status(),
                    'error' => $error,
                ]);

                throw new Exception(
                    $error['message'] ?? 'Failed to register business and user in third-party system',
                    $response->status()
                );
            }
        } catch (Exception $e) {
            Log::error('ThirdPartyApiService: Exception occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Get business details from third-party system
     *
     * @param int $businessId
     * @param string|null $token
     * @return array|null
     */
    public function getBusiness(int $businessId, ?string $token = null): ?array
    {
        try {
            $request = Http::timeout($this->timeout);

            if ($token) {
                $request->withToken($token);
            }

            $response = $request->get("{$this->baseUrl}/api/v1/businesses/{$businessId}");

            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? null;
            }

            return null;
        } catch (Exception $e) {
            Log::error('ThirdPartyApiService: Failed to get business', [
                'business_id' => $businessId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check if business exists by name or email
     *
     * @param string $name
     * @param string $email
     * @return array|null Returns business data if exists, null otherwise
     */
    public function checkBusinessExists(string $name, string $email): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/v1/businesses/check", [
                    'name' => $name,
                    'email' => $email,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success'] && isset($data['data'])) {
                    return $data['data'];
                }
            }

            return null;
        } catch (Exception $e) {
            Log::error('ThirdPartyApiService: Failed to check if business exists', [
                'name' => $name,
                'email' => $email,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check if user exists by email or username
     *
     * @param string $email
     * @param string|null $username
     * @return array|null Returns user data if exists, null otherwise
     */
    public function checkUserExists(string $email, ?string $username = null): ?array
    {
        try {
            $params = ['email' => $email];
            if ($username !== null) {
                $params['username'] = $username;
            }
            
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/v1/users/check", $params);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success'] && isset($data['exists']) && $data['exists'] === true) {
                    return $data['data'] ?? null;
                }
            }

            return null;
        } catch (Exception $e) {
            Log::error('ThirdPartyApiService: Failed to check if user exists', [
                'email' => $email,
                'username' => $username,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create user for existing business
     *
     * @param int $businessId
     * @param array $userData
     * @return array|null
     */
    public function createUserForBusiness(int $businessId, array $userData): ?array
    {
        try {
            $payload = [
                'business_id' => $businessId,
                'user_name' => $userData['name'] ?? '',
                'user_email' => $userData['email'] ?? '',
                'user_username' => $userData['username'] ?? '',
                'user_password' => $userData['password'] ?? '',
            ];

            Log::info('ThirdPartyApiService: Creating user for existing business', [
                'business_id' => $businessId,
                'user_email' => $payload['user_email'],
            ]);

            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/v1/businesses/{$businessId}/users", $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('ThirdPartyApiService: User created successfully for existing business', [
                    'business_id' => $businessId,
                    'user_id' => $data['data']['user']['id'] ?? null,
                ]);

                return $data['data'] ?? null;
            } else {
                $error = $response->json();
                Log::error('ThirdPartyApiService: Failed to create user for existing business', [
                    'status' => $response->status(),
                    'error' => $error,
                ]);

                throw new Exception(
                    $error['message'] ?? 'Failed to create user for existing business',
                    $response->status()
                );
            }
        } catch (Exception $e) {
            Log::error('ThirdPartyApiService: Exception occurred while creating user', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Login and get API token
     *
     * @param string $email
     * @param string $password
     * @return array|null
     */
    public function login(string $email, string $password): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/v1/auth/login", [
                    'email' => $email,
                    'password' => $password,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? null;
            }

            return null;
        } catch (Exception $e) {
            Log::error('ThirdPartyApiService: Failed to login', [
                'email' => $email,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate password reset token for a user and send email (handled by third-party system)
     *
     * @param string $email
     * @return array|null Returns the full response including success status
     */
    public function generatePasswordResetToken(string $email): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/v1/password/reset-token", [
                    'email' => $email,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                // Return the full response including success and data
                return $data;
            }

            Log::error('ThirdPartyApiService: Failed to generate password reset token', [
                'email' => $email,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('ThirdPartyApiService: Exception while generating password reset token', [
                'email' => $email,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get insurance company by code from third-party system
     *
     * @param string $code 8-character alphanumeric third party vendor code
     * @return array|null
     */
    public function getInsuranceCompanyByCode(string $code): ?array
    {
        try {
            Log::info('ThirdPartyApiService: Getting insurance company by code', [
                'code' => $code,
            ]);

            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/v1/businesses/by-code/{$code}");

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('ThirdPartyApiService: Insurance company found by code', [
                    'code' => $code,
                    'business_id' => $data['data']['business']['id'] ?? null,
                ]);

                return $data['data'] ?? null;
            } else {
                $error = $response->json();
                Log::warning('ThirdPartyApiService: Insurance company not found by code', [
                    'code' => $code,
                    'status' => $response->status(),
                    'error' => $error,
                ]);

                return null;
            }
        } catch (Exception $e) {
            Log::error('ThirdPartyApiService: Exception while getting insurance company by code', [
                'code' => $code,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create a business connection in the third-party system
     *
     * @param int $insuranceCompanyId The insurance company to connect to
     * @param int $connectedBusinessId The business being connected
     * @return array|null Returns connection data if successful, null otherwise
     */
    public function createBusinessConnection(int $insuranceCompanyId, int $connectedBusinessId, ?string $connectedBusinessName = null): ?array
    {
        try {
            $payload = [
                'insurance_company_id' => $insuranceCompanyId,
                'connected_business_id' => $connectedBusinessId,
            ];
            
            if ($connectedBusinessName) {
                $payload['connected_business_name'] = $connectedBusinessName;
            }

            Log::info('ThirdPartyApiService: Creating business connection', [
                'insurance_company_id' => $insuranceCompanyId,
                'connected_business_id' => $connectedBusinessId,
            ]);

            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/v1/businesses/connections", $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('ThirdPartyApiService: Business connection created successfully', [
                    'insurance_company_id' => $insuranceCompanyId,
                    'connected_business_id' => $connectedBusinessId,
                    'connection_id' => $data['data']['connection_id'] ?? null,
                ]);

                return $data['data'] ?? null;
            } else {
                $error = $response->json();
                Log::error('ThirdPartyApiService: Failed to create business connection', [
                    'status' => $response->status(),
                    'error' => $error,
                ]);

                return null;
            }
        } catch (Exception $e) {
            Log::error('ThirdPartyApiService: Exception while creating business connection', [
                'insurance_company_id' => $insuranceCompanyId,
                'connected_business_id' => $connectedBusinessId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Verify if a policy number exists for a given insurance company
     *
     * @param int $insuranceCompanyId
     * @param string $policyNumber
     * @param string|null $name Optional: Full name for tolerance-based verification
     * @param string|null $dateOfBirth Optional: Date of birth for tolerance-based verification
     * @return array|null Returns policy data if exists, null otherwise
     */
    public function verifyPolicyNumber(int $insuranceCompanyId, string $policyNumber, ?string $name = null, ?string $dateOfBirth = null): ?array
    {
        try {
            Log::info('=== Kashtre: verifyPolicyNumber START ===', [
                'insurance_company_id' => $insuranceCompanyId,
                'policy_number' => $policyNumber,
                'has_name' => !empty($name),
                'has_dob' => !empty($dateOfBirth),
                'name' => $name,
                'date_of_birth' => $dateOfBirth,
            ]);

            // Build query parameters if name or DOB are provided
            $queryParams = [];
            if ($name) {
                $queryParams['name'] = $name;
            }
            if ($dateOfBirth) {
                $queryParams['date_of_birth'] = $dateOfBirth;
            }

            $url = "{$this->baseUrl}/api/v1/policies/verify/{$insuranceCompanyId}/{$policyNumber}";
            if (!empty($queryParams)) {
                $url .= '?' . http_build_query($queryParams);
            }

            Log::info('Kashtre: Sending API request', [
                'url' => $url,
                'method' => 'GET',
                'query_params' => $queryParams,
            ]);

            $response = Http::timeout($this->timeout)
                ->get($url);

            Log::info('Kashtre: Received API response', [
                'status_code' => $response->status(),
                'response_headers' => $response->headers(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Kashtre: Policy number verified - SUCCESS', [
                    'insurance_company_id' => $insuranceCompanyId,
                    'policy_number' => $policyNumber,
                    'response_status_code' => $response->status(),
                    'response_data' => $data,
                    'exists' => $data['exists'] ?? false,
                    'verification_status' => $data['verification_status'] ?? null,
                    'verification_method' => $data['verification_method'] ?? null,
                    'has_warnings' => !empty($data['warnings']),
                    'warnings' => $data['warnings'] ?? [],
                ]);

                // Return full response data including warnings and verification status
                return $data;
            } else {
                $error = $response->json();
                Log::warning('Kashtre: Policy number verification FAILED', [
                    'insurance_company_id' => $insuranceCompanyId,
                    'policy_number' => $policyNumber,
                    'response_status_code' => $response->status(),
                    'response_body' => $response->body(),
                    'response_data' => $error,
                ]);

                return null;
            }
        } catch (Exception $e) {
            Log::error('Kashtre: Exception while verifying policy number', [
                'insurance_company_id' => $insuranceCompanyId,
                'policy_number' => $policyNumber,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        } finally {
            Log::info('=== Kashtre: verifyPolicyNumber END ===');
        }
    }

    /**
     * Verify client identity using name and date of birth (alternative verification)
     *
     * @param int $insuranceCompanyId
     * @param array $verificationData Array containing: name, date_of_birth
     * @return array|null Returns verification result with policy data if verified, null otherwise
     */
    public function verifyAlternativeIdentity(int $insuranceCompanyId, array $verificationData): ?array
    {
        try {
            // Only send name and date_of_birth (remove visit_id and other fields)
            $data = [
                'name' => $verificationData['name'] ?? null,
                'date_of_birth' => $verificationData['date_of_birth'] ?? null,
            ];
            
            // Remove null values
            $data = array_filter($data, function($value) {
                return $value !== null && $value !== '';
            });
            
            Log::info('=== Kashtre: verifyAlternativeIdentity START ===', [
                'insurance_company_id' => $insuranceCompanyId,
                'original_verification_data' => $verificationData,
                'filtered_data' => $data,
                'has_name' => !empty($data['name']),
                'has_dob' => !empty($data['date_of_birth']),
            ]);

            $url = "{$this->baseUrl}/api/v1/policies/verify/{$insuranceCompanyId}";
            
            Log::info('Kashtre: Sending alternative verification API request', [
                'url' => $url,
                'method' => 'POST',
                'payload' => $data,
            ]);

            $response = Http::timeout($this->timeout)
                ->post($url, $data);
            
            Log::info('Kashtre: Received alternative verification API response', [
                'status_code' => $response->status(),
                'response_headers' => $response->headers(),
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                Log::info('Kashtre: Alternative verification SUCCESS', [
                    'insurance_company_id' => $insuranceCompanyId,
                    'response_status_code' => $response->status(),
                    'response_data' => $responseData,
                    'verification_method' => $responseData['verification_method'] ?? null,
                    'verification_status' => $responseData['verification_status'] ?? null,
                    'exists' => $responseData['exists'] ?? false,
                    'warnings' => $responseData['warnings'] ?? [],
                ]);

                return $responseData;
            } else {
                $error = $response->json();
                Log::warning('Kashtre: Alternative verification FAILED', [
                    'insurance_company_id' => $insuranceCompanyId,
                    'response_status_code' => $response->status(),
                    'response_body' => $response->body(),
                    'response_data' => $error,
                ]);

                return $error;
            }
        } catch (Exception $e) {
            Log::error('Kashtre: Exception while verifying alternative identity', [
                'insurance_company_id' => $insuranceCompanyId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        } finally {
            Log::info('=== Kashtre: verifyAlternativeIdentity END ===');
        }
    }

    /**
     * Verify identity using visit ID
     *
     * @param int $insuranceCompanyId
     * @param string $visitId
     * @param array $additionalData Optional additional verification data
     * @return array|null Returns verification result with policy data if verified, null otherwise
     */
    public function verifyVisitIdentity(int $insuranceCompanyId, string $visitId, array $additionalData = []): ?array
    {
        try {
            $data = array_merge(['visit_id' => $visitId], $additionalData);

            Log::info('ThirdPartyApiService: Verifying visit identity', [
                'insurance_company_id' => $insuranceCompanyId,
                'visit_id' => $visitId,
            ]);

            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/v1/policies/verify-visit/{$insuranceCompanyId}", $data);

            if ($response->successful()) {
                $result = $response->json();
                
                Log::info('ThirdPartyApiService: Visit identity verified', [
                    'insurance_company_id' => $insuranceCompanyId,
                    'visit_id' => $visitId,
                    'verification_method' => $result['verification_method'] ?? null,
                    'verification_status' => $result['verification_status'] ?? null,
                ]);

                return $result;
            } else {
                $error = $response->json();
                Log::warning('ThirdPartyApiService: Visit identity verification failed', [
                    'insurance_company_id' => $insuranceCompanyId,
                    'visit_id' => $visitId,
                    'status' => $response->status(),
                    'error' => $error,
                ]);

                return $error;
            }
        } catch (Exception $e) {
            Log::error('ThirdPartyApiService: Exception while verifying visit identity', [
                'insurance_company_id' => $insuranceCompanyId,
                'visit_id' => $visitId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get insurance company settings by ID
     *
     * @param int $insuranceCompanyId
     * @return array|null Returns settings data if found, null otherwise
     */
    public function getInsuranceCompanySettings(int $insuranceCompanyId): ?array
    {
        try {
            Log::info('ThirdPartyApiService: Fetching insurance company settings', [
                'insurance_company_id' => $insuranceCompanyId,
            ]);

            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/v1/businesses/{$insuranceCompanyId}/settings");

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('ThirdPartyApiService: Insurance company settings retrieved', [
                    'insurance_company_id' => $insuranceCompanyId,
                    'success' => $data['success'] ?? false,
                ]);

                return $data['data'] ?? null;
            } else {
                $error = $response->json();
                Log::warning('ThirdPartyApiService: Failed to fetch insurance company settings', [
                    'insurance_company_id' => $insuranceCompanyId,
                    'status' => $response->status(),
                    'error' => $error,
                ]);

                return null;
            }
        } catch (Exception $e) {
            Log::error('ThirdPartyApiService: Exception while fetching insurance company settings', [
                'insurance_company_id' => $insuranceCompanyId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create client account in third-party system
     *
     * @param int $clientId Client ID in third-party system
     * @param int $insuranceCompanyId Insurance company ID
     * @return array|null Returns account data if successful, null otherwise
     */
    public function createClientAccount(int $clientId, int $insuranceCompanyId): ?array
    {
        try {
            Log::info('ThirdPartyApiService: Creating client account', [
                'client_id' => $clientId,
                'insurance_company_id' => $insuranceCompanyId,
            ]);

            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/v1/clients/{$clientId}/account", [
                    'insurance_company_id' => $insuranceCompanyId,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('ThirdPartyApiService: Client account created successfully', [
                    'client_id' => $clientId,
                    'account_number' => $data['data']['account']['account_number'] ?? null,
                ]);

                return $data['data'] ?? null;
            } else {
                $error = $response->json();
                Log::warning('ThirdPartyApiService: Failed to create client account', [
                    'client_id' => $clientId,
                    'status' => $response->status(),
                    'error' => $error,
                ]);

                return null;
            }
        } catch (Exception $e) {
            Log::error('ThirdPartyApiService: Exception while creating client account', [
                'client_id' => $clientId,
                'insurance_company_id' => $insuranceCompanyId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
