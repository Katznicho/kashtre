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
     * @return array|null
     */
    public function registerBusinessAndUser(array $businessData, array $userData): ?array
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
     * @param string $username
     * @return array|null Returns user data if exists, null otherwise
     */
    public function checkUserExists(string $email, string $username): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/v1/users/check", [
                    'email' => $email,
                    'username' => $username,
                ]);

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
}
