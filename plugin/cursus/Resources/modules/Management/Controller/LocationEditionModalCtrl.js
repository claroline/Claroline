/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/
/*global Translator*/

export default class LocationEditionModalCtrl {
  constructor($http, $uibModalInstance, title, location, callback) {
    this.$http = $http
    this.$uibModalInstance = $uibModalInstance
    this.title = title
    this.source = location
    this.callback = callback
    this.location = {
      name: this.source['name'],
      street: this.source['street'],
      streetNumber: this.source['street_number'],
      boxNumber: this.source['box_number'],
      pc: this.source['pc'],
      town: this.source['town'],
      country: this.source['country'],
      phone: this.source['phone']
    }
    this.locationErrors = {
      name: null,
      street: null,
      streetNumber: null,
      pc: null,
      town: null,
      country: null
    }
  }

  submit() {
    this.resetErrors()

    if (!this.location['name']) {
      this.locationErrors['name'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    } else {
      this.locationErrors['name'] = null
    }

    if (!this.location['street']) {
      this.locationErrors['street'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    } else {
      this.locationErrors['street'] = null
    }

    if (!this.location['streetNumber']) {
      this.locationErrors['streetNumber'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    } else {
      this.locationErrors['streetNumber'] = null
    }

    if (!this.location['pc']) {
      this.locationErrors['pc'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    } else {
      this.locationErrors['pc'] = null
    }

    if (!this.location['town']) {
      this.locationErrors['town'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    } else {
      this.locationErrors['town'] = null
    }

    if (!this.location['country']) {
      this.locationErrors['country'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    } else {
      this.locationErrors['country'] = null
    }

    if (this.isValid()) {
      const url = Routing.generate('api_put_cursus_location_edition', {location: this.source['id']})
      this.$http.post(url, {locationDatas: this.location}).then(d => {
        if (d['status'] === 200) {
          this.callback(d['data'])
          this.$uibModalInstance.close()
        }
      })
    }
  }

  resetErrors() {
    for (const key in this.locationErrors) {
      this.locationErrors[key] = null
    }
  }

  isValid() {
    let valid = true

    for (const key in this.locationErrors) {
      if (this.locationErrors[key]) {
        valid = false
        break
      }
    }

    return valid
  }
}
