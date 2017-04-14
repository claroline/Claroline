/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/

export default class CursusRegistrationCtrl {
  constructor($http) {
    this.$http = $http
    this.initialized = false
    this.cursusRoots = []
    this.hoveredCursusId = 0
    this.search = ''
    this.initialize()
  }

  initialize() {
    if (!this.initialized) {
      const route = Routing.generate('claroline_cursus_all_root_cursus_retrieve')
      this.$http.get(route).then(datas => {
        if (datas['status'] === 200) {
          this.cursusRoots = JSON.parse(datas['data'])
          this.initialized = true
        }
      })
    }
  }
}