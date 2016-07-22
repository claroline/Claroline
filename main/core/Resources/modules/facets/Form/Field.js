import NotBlank from '../../form/Validator/NotBlank'

export default {
  fields: [
    ['name', 'text', {validators: [new NotBlank()], label: 'name'}],
    ['is_required', 'checkbox', {label: 'mandatory'}],
    [
      'type',
      'select',
      {
        values: [
          // these values currently come from the Entity/Facet/FieldFacet class
          { value: 1, label: 'text'},
          { value: 2, label: 'number'},
          { value: 3, label: 'date'},
          { value: 4, label: 'radio'},
          { value: 5, label: 'select'},
          { value: 6, label: 'checkboxes'},
          { value: 7, label: 'country'},
          { value: 8, label: 'email'}
        ],
        default: 1,
        label: 'type',
        choice_value: 'value'
      }
    ]
  ]
}
