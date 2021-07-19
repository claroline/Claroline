import React from 'react'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors} from '#/plugin/cursus/tools/trainings/quota/store/selectors'
import {ToolPage} from '#/main/core/tool/containers/page'
import {QuotaList} from '#/plugin/cursus/tools/trainings/quota/components/list'

const QuotaTool = (props) =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('quotas', {}, 'cursus'),
      target: props.path
    }]}
    subtitle={trans('quotas', {}, 'cursus')}
  >
    <QuotaList
      name={selectors.STORE_NAME}
      path={props.path}
      definition={props.definition}
    />
  </ToolPage>

QuotaTool.propTypes = {
  path: T.string.isRequired,
  contextId: T.string,
  definition: T.array
}

export {
  QuotaTool
}
