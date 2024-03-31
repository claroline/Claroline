import React, {useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'
import {makeCancelable} from '#/main/app/api'
import {Tool} from '#/main/core/tool'

import {getIntegrations} from '#/main/core/integration'

const IntegrationTool = props => {
  const [pages, setPages] = useState([])

  useEffect(() => {
    const integrationPages = makeCancelable(getIntegrations())

    integrationPages.promise.then((loadedPages) => {
      setPages(loadedPages.map(app => ({
        name: app.default.name,
        icon: app.default.icon,
        path: `/${app.default.name}`,
        component: app.default.component
      })))
    })

    return integrationPages.cancel
  }, [props.path])

  return (
    <Tool
      {...props}
      styles={['claroline-distribution-main-core-administration-integration']}
      redirect={!isEmpty(pages) ? [
        {from: '/', exact: true, to: pages[0].path}
      ] : undefined}
      menu={pages.map(page => ({
        name: page.name,
        type: LINK_BUTTON,
        icon: page.icon,
        label: trans(page.name, {}, 'integration'),
        target: `${props.path}/${page.name}`
      }))}
      pages={pages}
    />
  )
}

IntegrationTool.propTypes = {
  path: T.string.isRequired
}

export {
  IntegrationTool
}
