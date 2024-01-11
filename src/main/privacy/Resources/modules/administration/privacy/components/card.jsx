import React from 'react'
import {PropTypes as T} from 'prop-types'

const PrivacyCard = props =>
  <div className="card mb-3">
    <div className="card-body pb-0">
      <h5 className="card-title mb-3">
        {props.title}
      </h5>
      <p className="card-text">
        {props.content}
      </p>
    </div>
  </div>

PrivacyCard.propTypes = {
  title: T.string.isRequired,
  content: T.oneOfType([T.string, T.element]).isRequired
}

export {
  PrivacyCard
}
