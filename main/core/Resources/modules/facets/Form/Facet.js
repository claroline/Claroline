import NotBlank from '../../form/Validator/NotBlank'

export default {
  fields: [
    ['name', 'text', {validators: [new NotBlank()], label: 'name'}],
    ['force_creation_form', 'checkbox', {label: 'display_at_registration'}],
    ['is_main', 'checkbox', {label: 'is_main_facet_label'}]
  ]
}
