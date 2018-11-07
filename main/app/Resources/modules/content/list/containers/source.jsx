import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'
import {ListParameters as ListParametersTypes} from '#/main/app/content/list/parameters/prop-types'

const ListSource = props => {
  // compute final list definition based an the source definition
  // and the configuration
  let computedDefinition = []
  const definition = get(props.source, 'parameters.definition')
  if (definition) {
    computedDefinition = definition.map(column => Object.assign({}, column, {
      filterable : column.filterable  && -1 !== props.parameters.availableFilters.indexOf(column.name),
      sortable   : column.sortable    && -1 !== props.parameters.availableSort.indexOf(column.name),
      displayable: column.displayable && -1 !== props.parameters.availableColumns.indexOf(column.name),
      displayed  : -1 !== props.parameters.columns.indexOf(column.name)
    }))
  }

  // compute final card
  let computedCard
  const baseCard = get(props.source, 'parameters.card')
  if (baseCard) {
    if (get(props.parameters, 'card.display')) {
      // append custom configuration to the card
      const ConfiguredCard = props => React.createElement(baseCard, merge({}, props, {
        display: get(props.parameters, 'card.display')
      }))

      computedCard = ConfiguredCard
    } else {
      computedCard = baseCard
    }
  }

  return (
    <ListData
      {...omit(props, 'source', 'parameters')}

      primaryAction={get(props.source, 'parameters.primaryAction')}
      actions={get(props.parameters, 'actions') ? get(props.source, 'parameters.actions') : undefined}

      definition={computedDefinition}
      card={computedCard}
      display={{
        current: props.parameters.display || listConst.DISPLAY_TILES_SM,
        available: props.parameters.availableDisplays
      }}
      searchMode={get(props.parameters, 'searchMode') || undefined}
      count={isEmpty(props.parameters) || props.parameters.count}
      selectable={!!get(props.parameters, 'actions')}
      filterable={isEmpty(props.parameters) || !isEmpty(props.parameters.availableFilters)}
      sortable={isEmpty(props.parameters) || !isEmpty(props.parameters.availableSort)}
      paginated={isEmpty(props.parameters) || props.parameters.paginated}
    />
  )
}

ListSource.propTypes = {
  // list source definition
  source: T.shape({
    parameters: T.shape({
      primaryAction: T.func,
      actions: T.func,
      definition: T.arrayOf(
        T.shape({}) // DataListProp
      ).isRequired,
      card: T.func
    })
  }),

  // list configuration
  parameters: T.shape(
    ListParametersTypes.propTypes
  )
}

export {
  ListSource
}
