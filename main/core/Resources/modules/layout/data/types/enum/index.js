import {EnumSearch} from '#/main/core/layout/data/types/enum/components/search.jsx'
import {EnumCell} from '#/main/core/layout/data/types/enum/components/table.jsx'

export const ENUM_TYPE = 'enum'

export const enumDefinition = {
  parse: (display) => display,
  render: (raw) => raw,
  validate: () => {
    return true
  },
  components: {
    table: EnumCell,
    search: EnumSearch
  }
}
