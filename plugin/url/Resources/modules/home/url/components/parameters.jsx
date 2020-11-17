import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {selectors} from '#/plugin/home/tools/home/editor/store/selectors'

import {UrlForm} from '#/plugin/url/components/form'

const UrlTabParameters = (props) =>
  <UrlForm
    embedded={true}
    disabled={props.readOnly}
    name={selectors.FORM_NAME}
    dataPart={`[${props.currentTabIndex}].parameters`}
    url={get(props.currentTab, 'parameters')}
    updateProp={props.update}
  />

UrlTabParameters.propTypes = {
  readOnly: T.bool,
  currentTabIndex: T.number.isRequired,
  currentTab: T.shape(
    TabTypes.propTypes
  ),
  update: T.func.isRequired
}

export {
  UrlTabParameters
}
