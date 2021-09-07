import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ContentHtml} from '#/main/app/content/components/html'

import {selectors} from '#/plugin/blog/resources/blog/store/selectors'

const InfobarComponent = props => {
  if (!isEmpty(props.infos)) {
    return (
      <div className="panel panel-default">
        <div className="panel-heading">
          <h2 className="panel-title">{trans('information')}</h2>
        </div>

        <ContentHtml className="panel-body">{props.infos}</ContentHtml>
      </div>
    )
  }

  return null
}

InfobarComponent.propTypes = {
  infos: T.string
}

const Infobar = connect(
  state => ({
    infos: !isEmpty(selectors.blog(state).data.options.data) ? selectors.blog(state).data.options.data.infos : selectors.blog(state).data.originalOptions.infos
  })
)(InfobarComponent)

export {Infobar}
