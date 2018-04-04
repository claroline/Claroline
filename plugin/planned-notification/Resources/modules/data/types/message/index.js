import {chain, string, notBlank} from '#/main/core/validation'

import {MessageFormGroup} from '#/plugin/planned-notification/data/types/message/components/message-form-group.jsx'

const MESSAGE_TYPE = 'message'

const messageDefinition = {
  meta: {
    type: MESSAGE_TYPE
  },

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
  MESSAGE_TYPE,
  messageDefinition
}