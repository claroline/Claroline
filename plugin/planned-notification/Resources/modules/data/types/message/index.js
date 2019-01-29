import {chain, string, notBlank} from '#/main/core/validation'

import {MessageInput} from '#/plugin/planned-notification/data/types/message/components/input'

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
    input: MessageInput
  }
}

export {
  dataType
}