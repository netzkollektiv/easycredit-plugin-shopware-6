const ApiService = Shopware.Classes.ApiService;

class EasyCreditRatenkaufApiCredentialsService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'easycredit') {
        super(httpClient, loginService, apiEndpoint);
    }

    validateApiCredentials(webshopId, apiPassword, apiSignature) {
        const headers = this.getBasicHeaders();

        return this.httpClient
            .get(
                `/_action/${this.getApiBasePath()}/validate-api-credentials`,
                {
                    params: { webshopId, apiPassword, apiSignature },
                    headers: headers
                }
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }
}

export default EasyCreditRatenkaufApiCredentialsService;