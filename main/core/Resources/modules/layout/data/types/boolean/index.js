import {parseBool, translateBool} from '#/main/core/layout/data/types/boolean/utils'
import {BooleanSearch} from '#/main/core/layout/data/types/boolean/components/search.jsx'
import {BooleanCell} from '#/main/core/layout/data/types/boolean/components/table.jsx'

export const BOOLEAN_TYPE = 'boolean'

export const booleanDefinition = {
  parse: (display) => parseBool(display),
  render: (raw) => translateBool(raw),

  validate: (value) => {
    try {
      parseBool(value)

      return true
    } catch (e) {
      return false
    }
  },
  components: {
    table: BooleanCell,
    search: BooleanSearch
  }
}
