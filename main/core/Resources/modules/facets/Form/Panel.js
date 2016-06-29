import NotBlank from '../../form/Validator/NotBlank'

export default {
  fields: [
    ['name', 'text', {validators: [new NotBlank()],  label: 'name'}],
    ['is_default_collapsed', 'checkbox', {label: 'collapse'}]
  ]
}
