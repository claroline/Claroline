/* global goog */

export default class RouterService {

  setRoutes(routes) {
    this.routes_ = new goog.structs.Map(routes)
  }

  getRoutes() {
    return this.routes_
  }

  setBaseUrl(baseUrl) {
    this.context_.base_url = baseUrl
  }

  getBaseUrl() {
    return this.context_.base_url
  }

  setPrefix(prefix) {
    this.context_.prefix = prefix
  }

  setScheme(scheme) {
    this.context_.scheme = scheme
  }

  getScheme() {
    return this.context_.scheme
  }

  setHost(host) {
    this.context_.host = host
  }

  getHost() {
    return this.context_.host
  }


  buildQueryParams(prefix, params, add) {
    var self = this
    var name
    var rbracket = new RegExp(/\[\]$/)

    if (params instanceof Array) {
      goog.array.forEach(params, function(val, i) {
        if (rbracket.test(prefix)) {
          add(prefix, val)
        } else {
          self.buildQueryParams(prefix + '[' + (typeof val === 'object' ? i : '') + ']', val, add)
        }
      })
    } else if (typeof params === 'object') {
      for (name in params) {
        this.buildQueryParams(prefix + '[' + name + ']', params[name], add)
      }
    } else {
      add(prefix, params)
    }
  }

  getRoute(name) {
    var prefixedName = this.context_.prefix + name
    if (!this.routes_.containsKey(prefixedName)) {
      // Check first for default route before failing
      if (!this.routes_.containsKey(name)) {
        throw new Error('The route "' + name + '" does not exist.')
      }
    } else {
      name = prefixedName
    }

    return (this.routes_.get(name))
  }

  generate(name, opt_params, absolute) {
    var route = (this.getRoute(name)),
      params = opt_params || {},
      unusedParams = goog.object.clone(params),
      url = '',
      optional = true,
      host = ''

    goog.array.forEach(route.tokens, function(token) {
      if ('text' === token[0]) {
        url = token[1] + url
        optional = false

        return
      }

      if ('variable' === token[0]) {
        var hasDefault = goog.object.containsKey(route.defaults, token[3])
        if (false === optional || !hasDefault || (goog.object.containsKey(params, token[3]) && params[token[3]] != route.defaults[token[3]])) {
          var value

          if (goog.object.containsKey(params, token[3])) {
            value = params[token[3]]
            goog.object.remove(unusedParams, token[3])
          } else if (hasDefault) {
            value = route.defaults[token[3]]
          } else if (optional) {
            return
          } else {
            throw new Error('The route "' + name + '" requires the parameter "' + token[3] + '".')
          }

          var empty = true === value || false === value || '' === value

          if (!empty || !optional) {
            var encodedValue = encodeURIComponent(value).replace(/%2F/g, '/')

            if ('null' === encodedValue && null === value) {
              encodedValue = ''
            }

            url = token[1] + encodedValue + url
          }

          optional = false
        } else if (hasDefault) {
          goog.object.remove(unusedParams, token[3])
        }

        return
      }

      throw new Error('The token type "' + token[0] + '" is not supported.')
    })

    if (url === '') {
      url = '/'
    }

    goog.array.forEach(route.hosttokens, function (token) {
      var value

      if ('text' === token[0]) {
        host = token[1] + host

        return
      }

      if ('variable' === token[0]) {
        if (goog.object.containsKey(params, token[3])) {
          value = params[token[3]]
          goog.object.remove(unusedParams, token[3])
        } else if (goog.object.containsKey(route.defaults, token[3])) {
          value = route.defaults[token[3]]
        }

        host = token[1] + value + host
      }
    })

    url = this.context_.base_url + url
    if (goog.object.containsKey(route.requirements, '_scheme') && this.getScheme() != route.requirements['_scheme']) {
      url = route.requirements['_scheme'] + '://' + (host || this.getHost()) + url
    } else if (host && this.getHost() !== host) {
      url = this.getScheme() + '://' + host + url
    } else if (absolute === true) {
      url = this.getScheme() + '://' + this.getHost() + url
    }

    if (goog.object.getCount(unusedParams) > 0) {
      var prefix
      var queryParams = []
      var add = function(key, value) {
        // if value is a function then call it and assign it's return value as value
        value = (typeof value === 'function') ? value() : value

        // change null to empty string
        value = (value === null) ? '' : value

        queryParams.push(encodeURIComponent(key) + '=' + encodeURIComponent(value))
      }

      for (prefix in unusedParams) {
        this.buildQueryParams(prefix, unusedParams[prefix], add)
      }

      url = url + '?' + queryParams.join('&').replace(/%20/g, '+')
    }

    return url
  }
}
