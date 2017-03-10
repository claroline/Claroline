import React, {PropTypes as T} from 'react'

export const TextContentModal = (props) =>
  <div className="text-content-modal">
    {props.data &&
      <div dangerouslySetInnerHTML={{ __html: props.data }}>
      </div>
    }
  </div>

TextContentModal.propTypes = {
  data: T.string,
  type: T.string.isRequired
}
