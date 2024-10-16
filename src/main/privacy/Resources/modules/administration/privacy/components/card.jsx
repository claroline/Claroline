import React from 'react'
import {PropTypes as T} from 'prop-types'

const PrivacyCard = props =>
  <div className="card mb-3">
    <div className="card-body">
      <h5 className="card-title">
        {props.title}
      </h5>

      {props.content}
    </div>
  </div>

PrivacyCard.propTypes = {
  title: T.string.isRequired,
  content: T.oneOfType([T.string, T.element]).isRequired
}

export {
  PrivacyCard
}
