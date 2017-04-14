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

export default class SessionMessageModalCtrl {
  constructor($http, $uibModalInstance, CourseService, session) {
    this.$http = $http
    this.$uibModalInstance = $uibModalInstance
    this.session = session
    this.message = {
      object: null,
      content: null,
      internal: true,
      external: true
    }
    this.messageErrors = {
      object: null,
      content: null
    }
    this.tinymceOptions = CourseService.getTinymceConfiguration()
  }

  submit() {
    this.resetErrors()

    if (!this.message['object']) {
      this.messageErrors['object'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    } else {
      this.messageErrors['object'] = null
    }

    if (!this.message['content']) {
      this.messageErrors['content'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    } else {
      this.messageErrors['content'] = null
    }

    if (this.isValid()) {
      const url = Routing.generate('api_post_session_message_send', {session: this.session['id']})
      this.$http.post(url, {messageDatas: this.message}).then(d => {
        if (d['status'] === 200) {
          this.$uibModalInstance.close()
        }
      })
    }
  }

  resetErrors() {
    for (const key in this.messageErrors) {
      this.messageErrors[key] = null
    }
  }

  isValid() {
    let valid = true

    for (const key in this.messageErrors) {
      if (this.messageErrors[key]) {
        valid = false
        break
      }
    }

    return valid
  }
}
