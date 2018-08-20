import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/core/translation'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'
import isEmpty from 'lodash/isEmpty'
import {selectors} from '#/plugin/blog/resources/blog/store'

const InfobarComponent = props =>
  <div className="panel panel-default">
    <div className="panel-heading">
      <h2 className="panel-title">{trans('infobar', {}, 'icap_blog')}</h2>
    </div>
    {!isEmpty(props.infos) &&
      <HtmlText className="panel-body">{props.infos}</HtmlText>
    }

  </div>

InfobarComponent.propTypes = {
  infos: T.string
}

const Infobar = connect(
  state => ({
    infos: !isEmpty(selectors.blog(state).data.options.data) ? selectors.blog(state).data.options.data.infos : selectors.blog(state).data.originalOptions.infos
  })
)(InfobarComponent)

export {Infobar}
