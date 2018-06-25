import React from 'react'
import {Row, Col} from 'react-bootstrap'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import {trans} from '#/main/core/translation'
import {Version} from '#/plugin/wiki/resources/wiki/history/components/version'

const VersionCompareComponent = props =>
  <div className="wiki-version-compare">
    {props.compareSet.length === 2 &&
    <Row>
      <Col md={6}>
        <Version version={props.compareSet[0]}/>
      </Col>
      <Col md={6}>
        <Version version={props.compareSet[1]}/>
      </Col>
    </Row>
    }
    {props.compareSet.length === 2 &&
    <Row className="diff-html-legend">
      <Col md={4} className="text-center">
        <span className="diff-html-added diff-html-simple"/>
        {trans('added', {}, 'icap_wiki')}
      </Col>
      <Col md={4} className="text-center">
        <span className="diff-html-removed diff-html-simple"/>
        {trans('deleted', {}, 'icap_wiki')}
      </Col>
      <Col md={4} className="text-center">
        <span className="diff-html-changed diff-html-simple"/>
        {trans('changed', {}, 'icap_wiki')}
      </Col>
    </Row>
    }
  </div>

VersionCompareComponent.propTypes = {
  compareSet: T.arrayOf(T.object).isRequired,
  section: T.object.isRequired
}

const VersionCompare = connect(
  state => ({
    compareSet: state.history.compareSet,
    section: state.history.currentSection
  })
)(VersionCompareComponent)

export {
  VersionCompare
}