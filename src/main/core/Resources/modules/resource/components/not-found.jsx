import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {hasPermission} from '#/main/app/security'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentNotFound} from '#/main/app/content/components/not-found'
import {ContextPage} from '#/main/app/context'
import {selectors as contextSelectors} from '#/main/app/context'
import {selectors as toolSelectors} from '#/main/core/tool'

import {selectors} from '#/main/core/resource/store'

const ResourceNotFound = (props) => {
  const contextPath = useSelector(contextSelectors.path)
  const toolPath = useSelector(toolSelectors.path)
  const toolData = useSelector(toolSelectors.tool)
  const embedded = useSelector(selectors.embedded)

  if (embedded) {
    return (
      <ContentNotFound
        size="lg"
        title={trans('not_found', {}, 'resource')}
        description={trans('not_found_desc', {}, 'resource')}
      />
    )
  }

  return (
    <ContextPage
      breadcrumb={[
        {
          label: trans('resources', {}, 'tools'),
          target: toolPath
        }
      ]}
    >
      <ContentNotFound
        size="lg"
        title={trans('not_found', {}, 'resource')}
        description={trans('not_found_desc', {}, 'resource')}
      >
        <div className="mt-5 d-flex gap-2 justify-content-center">
          <Button
            {...props.secondaryAction}
            className="btn btn-link"
            type={LINK_BUTTON}
            icon="fa fa-arrow-left"
            label={trans('back_home', {}, 'actions')}
            target={contextPath}
            exact={true}
          />

          {hasPermission('open', toolData) &&
            <Button
              className="btn btn-primary btn-wave"
              type={LINK_BUTTON}
              label={trans('browse-resources', {}, 'actions')}
              target={toolPath}
              exact={true}
            />
          }
        </div>
      </ContentNotFound>
    </ContextPage>
  )
}

export {
  ResourceNotFound
}
