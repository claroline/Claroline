import {trans} from '#/main/app/intl/translation'
import {chain, lengthInRange, match, string} from '#/main/core/validation'

import {MediasDisplay} from '#/plugin/exo/data/types/medias/components/display'
import {MediasInput} from '#/plugin/exo/data/types/medias/components/input'

const dataType = {
  name: 'medias',
  meta: {},

  //validate: (value, options) => chain(value, options, [string, match, lengthInRange]),
  components: {
    details: MediasDisplay,

    // new api
    input: MediasInput,
    display: MediasDisplay
  }
}

export {
  dataType
}
