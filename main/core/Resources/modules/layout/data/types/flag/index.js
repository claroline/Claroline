import merge from 'lodash/merge'

import {t} from '#/main/core/translation'
import {booleanDefinition} from '#/main/core/layout/data/types/boolean/index'
import {FlagCell} from '#/main/core/layout/data/types/flag/components/table.jsx'

export const FLAG_TYPE = 'flag'

// It expends the default `boolean` type.
// The main difference with the `boolean` type is that `flag` props are only displayed if true.
export const flagDefinition = merge({}, booleanDefinition, {
  render: (raw) => raw ? t('yes') : null,
  components: {
    table: FlagCell
  }
})
