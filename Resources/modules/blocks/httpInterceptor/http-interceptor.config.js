config.$inject = [ '$httpProvider' ]

export default function config ($httpProvider) {
  $httpProvider.interceptors.push('httpInterceptor')
}
