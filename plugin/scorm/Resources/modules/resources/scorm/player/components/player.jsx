import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {Routes} from '#/main/app/router'
import {asset} from '#/main/app/config/asset'

import {Scorm as ScormTypes, Sco as ScoTypes} from '#/plugin/scorm/resources/scorm/prop-types'
import {getFirstOpenableSco} from '#/plugin/scorm/resources/scorm/utils'

const ScormIframe = (props) =>
  <div
    className="scorm-content-container"
    style={props.ratio ? {
      position: 'relative',
      paddingBottom: `${props.ratio}%`
    } : {}}
  >
    <iframe
      className="scorm-iframe"
      src={`${props.baseUrl}/${props.sco.data.entryUrl}${props.sco.data.parameters ? props.sco.data.parameters : ''}`}
    />
  </div>

ScormIframe.propTypes = {
  ratio: T.number,
  baseUrl: T.string.isRequired,
  sco: T.shape(
    ScoTypes.propTypes
  ).isRequired
}

const Player = (props) => {
  const firstSco = getFirstOpenableSco(props.scos)

  return (
    <Routes
      path={props.path}
      redirect={[
        {from: '/play', to: `/play/${firstSco.id}`, disabled: isEmpty(firstSco)}
      ]}
      routes={[
        {
          path: '/play/:id',
          onEnter(params = {}) {
            const currentSco = props.scos.find(sco => sco.id === params.id)
            if (currentSco) {
              props.initializeScormAPI(currentSco, props.scorm, props.trackings, props.currentUser)
            }
          },
          render(routeProps) {
            const currentSco = props.scos.find(sco => sco.id === routeProps.match.params.id)
            if (currentSco && !isEmpty(currentSco.data.entryUrl)) {
              return (
                <ScormIframe
                  ratio={get(props.scorm, 'ratio')}
                  baseUrl={`${asset('uploads/scorm/')}${props.workspaceUuid}/${props.scorm.hashName}`}
                  sco={currentSco}
                />
              )
            }

            routeProps.history.push(props.path+'/play')

            return null
          }
        }
      ]}
    />
  )
}

Player.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  scorm: T.shape(
    ScormTypes.propTypes
  ),
  trackings: T.object,
  scos: T.arrayOf(T.shape(
    ScoTypes.propTypes
  )).isRequired,
  workspaceUuid: T.string.isRequired,
  initializeScormAPI: T.func.isRequired
}

export {
  Player
}
