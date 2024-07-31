<?php

namespace Saulmoralespa\Aliaddo;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Utils;

class Client
{
    const API_BASE_URL = "https://app.aliaddo.net/";
    const API_VERSION = "v1";
    private static string $tokenFilePath = "token.json";

    public function __construct(
        private $token,
    ) {
    }

    public function client(): GuzzleClient
    {
        return new GuzzleClient([
            "base_uri" => self::API_BASE_URL
        ]);
    }

    /**
     * @throws \Exception|GuzzleException
     */
    public function getProducts(array $queries = []): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/items", [
            "query" => $queries
        ]);
    }

    /**
     * @throws \Exception|GuzzleException
     */
    public function getProductById(string $id): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/items/$id");
    }

    /**
     * @throws \Exception|GuzzleException
     */
    public function createProduct(array $data): array
    {
        return $this->makeRequest("POST", self::API_VERSION . "/items", [
            "json" => $data
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function updateProduct(string $id, array $data): array
    {
        return $this->makeRequest("PUT", self::API_VERSION . "/items/$id", [
            "json" => $data
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function deleteProduct(string $productId): array
    {
        return $this->makeRequest("DELETE", self::API_VERSION . "/items/$productId");
    }

    /**
     * @throws GuzzleException
     */
    public function createClient(array $data): array
    {
        return $this->makeRequest("POST", self::API_VERSION . "/people", [
            "json" => $data
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getClients(array $queries = []): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/people", [
            "query" => $queries
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getClientById(string $id): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/people/$id");
    }

    /**
     * @throws GuzzleException
     */
    public function updateClient(string $id, array $data): array
    {
        return $this->makeRequest("PUT", self::API_VERSION . "/people/$id", [
            "json" => $data
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function deleteClient(string $clientId): array
    {
        return $this->makeRequest("DELETE", self::API_VERSION . "/people/$clientId");
    }

    /**
     * @throws GuzzleException
     */
    public function getInvoices(array $queries): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/invoices", [
            "query" => $queries
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getInvoiceById(string $id): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/invoices/$id");
    }

    /**
     * @throws GuzzleException
     */
    public function createInvoice(array $data): array
    {
        return $this->makeRequest("POST", self::API_VERSION . "/invoices", [
            "json" => $data
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function updateInvoice(string $id, array $data): array
    {
        return $this->makeRequest("PUT", self::API_VERSION . "/invoices/$id", [
            "json" => $data
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function payInvoice(string $id, array $data): array
    {
        return $this->makeRequest("POST", self::API_VERSION . "/invoices/$id/payments", [
            "json" => $data
        ]);
    }
    /**
     * @throws GuzzleException
     */
    public function annulInvoice(string $id, array $queries = []): array
    {
        return $this->makeRequest("PATCH", self::API_VERSION . "/invoices/$id/void", [
            "query" => $queries
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getQuotes(array $queries): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/quotes", [
            "query" => $queries
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getQuoteById(string $id): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/quotes/$id");
    }

    /**
     * @throws GuzzleException
     */
    public function createQuote(array $data): array
    {
        return $this->makeRequest("POST", self::API_VERSION . "/quotes", [
            "json" => $data
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function updateQuote(string $id, array $data): array
    {
        return $this->makeRequest("PUT", self::API_VERSION . "/quotes/$id", [
            "json" => $data
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function anullQuote(string $id): array
    {
        return $this->makeRequest("PUT", self::API_VERSION . "/quotes/$id/void");
    }

    /**
     * @throws GuzzleException
     */
    public function getBranches(): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/branches");
    }

    /**
     * @throws GuzzleException
     */
    public function getDocumentTypes(array $queries): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/document-types", [
            "query" => $queries
        ]);
    }


    /**
     * @throws GuzzleException
     */
    public function getPdfInvoice(string $id): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/invoices/$id/pdf");
    }

    /**
     * @throws GuzzleException
     */
    public function getSellers(): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/people/sellers");
    }

    /**
     * @throws GuzzleException
     */
    public function getPaymentsMethods(array $queries): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/payment-types", [
            "query" => $queries
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getCostCenters(array $queries): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/cost-centers", [
            "query" => $queries
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getTaxes(array $queries = []): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/taxes", [
            "query" => $queries
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getTaxById(string $taxId): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/taxes/$taxId");
    }

    /**
     * @throws GuzzleException
     */
    public function getWithholdings(): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/withholdings");
    }

    /**
     * @throws GuzzleException
     */
    public function getWarehouses(array $queries = []): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/warehouses", [
            "query" => $queries
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getCategories(): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/item-categories");
    }

    /**
     * @throws GuzzleException
     */
    public function getMeasuringUnits(): array
    {
        return $this->makeRequest("GET", self::API_VERSION . "/measuring-units");
    }

    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    private function makeRequest(string $method, string $uri, array $options = []): array
    {
        try {
            $options["headers"] = [
                "Authorization" => "Bearer " . $this->token,
                "Accept" => "application/json"
            ];

            $options = [
                ...$options
            ];

            $res = $this->client()->request($method, $uri, $options);
            $content =  $res->getBody()->getContents();
            return self::responseArray($content);
        } catch (RequestException $exception) {
            $errorMessage = $this->handleErrors($exception);
            throw new \Exception($errorMessage);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    public static function responseArray(string $content): array
    {
        return Utils::jsonDecode($content, true);
    }

    public function handleErrors(RequestException $exception): ?string
    {
        $content = $exception->getResponse()->getBody()->getContents();
        $code = $exception->getCode();

        if ($content !== strip_tags($content) && !$this->jsonValidate($content)) {
            $errorMessage = $this->getErrorMessageByCode($code);
        } else {
            $errorMessage = $content;
        }

        return $errorMessage;
    }

    protected function jsonValidate(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    private function getErrorMessageByCode(int $code): string
    {
        $errors = [
            400 => "Petición incorrecta",
            401 => "No autorizado",
            403 => "Prohibido",
            404 => "No encontrado",
            405 => "Método no permitido",
            422 => "Entidad no procesable",
            429 => "Demasiadas solicitudes",
            500 => "Error interno del servidor",
            503 => "Servicio no disponible"
        ];

        return $errors[$code] ?? "Error desconocido";
    }
}
