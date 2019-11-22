import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {matchPath} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Summary} from '#/main/app/content/components/summary'

import {Scorm as ScormTypes, Sco as ScoTypes} from '#/plugin/scorm/resources/scorm/prop-types'

const PlayerMenu = props => {
  function generateSummary(scos) {
    return scos
      .map(sco => ({
        label: sco.data.title,
        type: LINK_BUTTON,
        target: `${props.path}/play/${sco.id}`,
        active: !!matchPath(props.location.pathname, {path: `${props.path}/play/${sco.id}`}),
        disabled: isEmpty(sco.data.entryUrl),
        onClick: props.autoClose,
        children: sco.children && sco.children.length > 0 ? generateSummary(sco.children) : []
      }))
  }

  if (1 < props.scos.length) {
    return (
      <Summary
        links={generateSummary(props.scorm.scos)}
      />
    )
  }

  return null
}

PlayerMenu.propTypes = {
  location: T.shape({
    pathname: T.string.isRequired
  }),
  path: T.string.isRequired,
  scorm: T.shape(
    ScormTypes.propTypes
  ),
  scos: T.arrayOf(T.shape(
    ScoTypes.propTypes
  )).isRequired,
  autoClose: T.func.isRequired
}


export {
  PlayerMenu
}
