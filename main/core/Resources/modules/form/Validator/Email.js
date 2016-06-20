export default class Email {
  validate (email) {
      //http://stackoverflow.com/questions/46155/validate-email-address-in-javascript
      var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(email);
  }

  getErrorMessage (el) {
    return Translator.trans('email_not_valid', {}, 'validators')
  }
}
