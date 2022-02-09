import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans, fileSize} from '#/main/app/intl'
import {CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {Alert} from '#/main/app/alert/components/alert'
import {Toolbar} from '#/main/app/action'
import {ContentHtml} from '#/main/app/content/components/html'
import {route} from '#/main/core/workspace/routing'

const PlayerOverview = (props) =>
  <Fragment>
    {get(props.resourceNode, 'meta.description') &&
      <div className="panel panel-default" style={{marginTop: 20}}>
        <ContentHtml className="panel-body">{get(props.resourceNode, 'meta.description')}</ContentHtml>
      </div>
    }

    <div className="well well-sm component-container" style={{marginTop: !get(props.resourceNode, 'meta.description') ? 20 : 0}}>
      <span className="fa fa-fw fa-file icon-with-text-right" />
      {props.file.name}
      <b className="pull-right">
        {fileSize(props.file.size)+trans('bytes_short')}
      </b>
    </div>

    <Alert type="info">{trans('auto_download_help', {}, 'file')}</Alert>

    <Toolbar
      className="component-container"
      buttonName="btn btn-block btn-emphasis"
      toolbar="download home"
      actions={[
        {
          name: 'download',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-download',
          label: trans('download', {}, 'actions'),
          callback: () => props.download(props.resourceNode),
          primary: true
        }, {
          name: 'home',
          type: URL_BUTTON, // we require an URL_BUTTON here to escape the embedded resource router
          icon: 'fa fa-fw fa-home',
          label: trans('return-home', {}, 'actions'),
          target: '#'+route(props.workspace),
          displayed: !!props.workspace
        }
      ]}
    />
  </Fragment>

PlayerOverview.propTypes = {
  file: T.shape({
    name: T.string.isRequired,
    size: T.number
  }).isRequired,
  resourceNode: T.shape({
    name: T.string.isRequired,
    meta: T.shape({
      description: T.string
    })
  }),
  workspace: T.object,
  download: T.func.isRequired
}

export {
  PlayerOverview
}
