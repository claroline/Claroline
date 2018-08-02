import {chain, string, notBlank} from '#/main/core/validation'

import {MessageFormGroup} from '#/plugin/planned-notification/data/message/components/message-form-group'

const dataType = {
  name: 'message',
  validate: (value, options) => chain(value, options, [(value) => {
    if (value) {
      const error = chain(value.id, {isHtml: false}, [string, notBlank])

      if (error) {
        return error
      }
    }
  }]),

  components: {
    form: MessageFormGroup
  }
}

export {
  dataType
}