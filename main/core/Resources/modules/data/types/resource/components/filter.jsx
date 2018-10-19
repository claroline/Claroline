import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_RESOURCE_EXPLORER} from '#/main/core/resource/modals/explorer'

// TODO : reuse explorer config (title, root, filters, etc.)

const ResourceFilter = (props) =>
  <span className="resource-filter">
    {props.search}

    <Button
      className="btn-filter"
      type={MODAL_BUTTON}
      tooltip="left"
      icon="fa fa-folder"
      label={trans('select_resource')}
      size="sm"
      modal={[MODAL_RESOURCE_EXPLORER, {
        selectAction: (selected) => ({
          type: CALLBACK_BUTTON,
          callback: () => props.updateSearch(selected[0].autoId)
        })
      }]}
    />
  </span>

ResourceFilter.propTypes = {
  /*search: T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired
  }),*/
  search: T.string,
  isValid: T.bool.isRequired,
  updateSearch: T.func.isRequired
}

export {
  ResourceFilter
}
