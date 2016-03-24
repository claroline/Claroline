export default class UserAPIService {
    constructor($http, ClarolineAPIService) {
        this.$http = $http
        this.ClarolineAPIService = ClarolineAPIService
    }

    removeFromCsv(formData) {
        console.log(formData)
        return this.$http.post(
            Routing.generate('api_csv_remove_user'),
            formData,
            {
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
        }
        )
    }
}
