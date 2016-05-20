export default class FormBuilderService {
  constructor ($httpParamSerializerJQLike) {
    this.$httpParamSerializerJQLike = $httpParamSerializerJQLike
  }

  // copy pasted from ClarolineAPIService as it's probably going to change and the other one should be removed someday
  formSerialize (formName, parameters) {
    var data = {}
    var serialized = angular.copy(parameters)
    data[formName] = serialized

    return this.$httpParamSerializerJQLike(data)
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
