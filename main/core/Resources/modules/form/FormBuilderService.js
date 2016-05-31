export default class FormBuilderService {
  constructor ($httpParamSerializerJQLike, Upload, $http) {
    this.$httpParamSerializerJQLike = $httpParamSerializerJQLike
    this.Upload = Upload
    this.$http = $http
  }

  submit(url, parameters, method = 'POST') {

      if (method === 'POST') {
          return this.Upload.upload({
              url: url,
              data: parameters,
              method: method
              //headers: {'Content-type': 'application/x-www-form-urlencoded' }
          })
      } else {
          //https://github.com/danialfarid/ng-file-upload/issues/1037
          //this is a patch for the put request that doens't work until we can safely remove it.
          //won't handle file upload until a better solution is found
          return this.$http.put(
              url,
              this.$httpParamSerializerJQLike(parameters),
              {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
          )
      }
  }

  // copy pasted from ClarolineAPIService. Where should it go ?
  generateQueryString (array, name) {
    var qs = ''

    for (var i = 0; i < array.length; i++) {
      var id = (array[i].id) ? array[i].id : array[i]
      qs += name + '[]=' + id + '&'
    }

    return qs
  }
}
