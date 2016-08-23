import NotBlank from '#/main/core/form/Validator/NotBlank'

/* global Translator */

export default {
  fields: [
    ['lang', 'lang', {label: Translator.trans('lang', {}, 'platform'), validators: [new NotBlank()]}],
    ['is_default', 'checkbox', {label: Translator.trans('is_default', {}, 'platform')}],
    ['track', 'file', {label: Translator.trans('track', {}, 'platform'), validators: [new NotBlank()]}]
  ]
}
