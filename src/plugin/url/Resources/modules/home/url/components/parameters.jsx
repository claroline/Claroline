import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {selectors} from '#/plugin/home/tools/home/editor/store/selectors'
import {getFormDataPart} from '#/plugin/home/tools/home/editor/utils'

import {UrlForm} from '#/plugin/url/components/form'

const UrlTabParameters = (props) =>
  <UrlForm
    embedded={true}
    disabled={props.readOnly}
    name={selectors.FORM_NAME}
    dataPart={`${getFormDataPart(props.currentTab.id, props.tabs)}.parameters`}
    url={get(props.currentTab, 'parameters')}
    updateProp={props.update}
  />

UrlTabParameters.propTypes = {
  readOnly: T.bool,
  currentTab: T.shape(
    TabTypes.propTypes
  ),
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  update: T.func.isRequired
}

export {
  UrlTabParameters
}
