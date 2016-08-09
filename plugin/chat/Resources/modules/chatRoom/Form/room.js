import NotBlank from '#/main/core/form/Validator/NotBlank'

export default {
  fields: [
    ['room_name', 'text', {label: 'room_name', disabled: true}],
    [
      'room_type',
      'select',
      {
        values: [
          { value: 0, label: 'text_only'},
          { value: 1, label: 'audio_only'},
          { value: 2, label: 'audio_video'}
        ],
        choice_value: 'value',
        translation_domain: 'chat'
      }
    ],
    [
      'room_status',
      'select',
      {
        values: [
          { value: 1, label: 'open'},
          { value: 2, label: 'closed'}
        ],
        choice_value: 'value',
        translation_domain: 'chat'
      }
    ]
  ]
}
