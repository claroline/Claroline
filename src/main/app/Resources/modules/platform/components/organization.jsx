import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {Thumbnail} from '#/main/app/components/thumbnail'

import {selectors} from '#/main/app/platform/store'
import {MODAL_PLATFORM_ORGANIZATIONS} from '#/main/app/platform/modals/organizations'

const PlatformOrganization = () => {
  const currentOrganization = useSelector(selectors.currentOrganization)

  return (
    <Button
      className="app-context-btn"
      type={MODAL_BUTTON}
      modal={[MODAL_PLATFORM_ORGANIZATIONS]}
      label={currentOrganization.name + ' ' + trans('(Cliquez pour changer d\'organization)')}
      tooltip="right"
    >
      <Thumbnail
        size="sm"
        thumbnail={currentOrganization.thumbnail}
        name={currentOrganization.name}
        square={true}
      />
    </Button>
  )
}

export {
  PlatformOrganization
}