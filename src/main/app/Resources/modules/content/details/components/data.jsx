import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {DataDetailsSection as DataDetailsSectionTypes} from '#/main/app/content/details/prop-types'
import {createDetailsDefinition} from '#/main/app/content/details/utils'
import {DescriptionList} from '#/main/app/data/components/description-list'
import {DetailsSection} from '#/main/app/content/details/components/section'

const DetailsData = props => {
  const sections = createDetailsDefinition(props.definition, props.data)

  const primarySections = 1 === sections.length ? [sections[0]] : sections.filter(section => section.primary)
  const otherSections = 1 !== sections.length ? sections.filter(section => !section.primary) : []

  return (
    <div className={classes('data-details d-flex flex-column gap-5', props.className)}>
      {primarySections.map((primarySection, index) =>
        <DetailsSection
          key={primarySection.title}
          level={props.level}
          displayLevel={5}
          title={primarySection.title}
          hideTitle={0 === index || primarySection.hideTitle}
          description={primarySection.description}
          help={primarySection.help}
          actions={primarySection.actions}
        >
          <DescriptionList
            className="mb-0"
            inline={true}
            fields={primarySection.fields}
            more={primarySection.more}
            data={props.data}
            loaded={props.loaded}
          />
        </DetailsSection>
      )}

      {otherSections.map((section) =>
        <DetailsSection
          key={section.title}
          level={props.level}
          displayLevel={5}
          title={section.title}
          hideTitle={section.hideTitle}
          description={section.description}
          help={section.help}
          actions={section.actions}
        >
          <DescriptionList
            className="mb-0"
            inline={true}
            fields={section.fields}
            more={section.more}
            data={props.data}
            loaded={props.loaded}
          />
        </DetailsSection>
      )}
    </div>
  )
}

DetailsData.propTypes = {
  id: T.string,
  className: T.string,
  level: T.number,
  data: T.object,
  loaded: T.bool,
  definition: T.arrayOf(T.shape(
    DataDetailsSectionTypes.propTypes
  )).isRequired
}

DetailsData.defaultProps = {
  level: 2,
  loaded: true,
  data: {}
}

export {
  DetailsData
}
