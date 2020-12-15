import React from 'react'
import {PropTypes as T} from 'prop-types'
import {WikiSection} from '#/plugin/wiki/resources/wiki/player/components/wiki-section'

const WikiSectionTree = props =>
  <div className="wiki-section-container">
    {
      props.sections.tree.children &&
      props.sections.tree.children.map(
        (section, index) =>
          <WikiSection
            key={section.id}
            num={[index + 1]}
            section={section}
          />
      )
    }
  </div>
  
WikiSectionTree.propTypes = {
  'sections': T.object.isRequired
}

export {
  WikiSectionTree
}