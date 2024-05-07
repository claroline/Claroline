import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import classes from 'classnames'

import {trans} from '#/main/app/intl'
import {asset} from '#/main/app/config'
import {ContentSummary} from '#/main/app/content/components/summary'
import {DataCard} from '#/main/app/data/components/card'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors as resourceSelectors} from '#/main/core/resource'
import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'
import {TreeData} from '#/main/app/content/list/tree/components/data'
import {EditorPage} from '#/main/app/editor'


const StepCard = (props) =>
  <DataCard
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail) : null}
    icon={classes({
      'fa fa-fw fa-folder': true
    })}
    title={props.data.title}
  />

StepCard.propTypes = {
  data: T.shape(
    StepTypes.propTypes
  )
}

const EditorScenario = (props) => {
  const steps = useSelector((state) => formSelectors.value(formSelectors.form(state, resourceSelectors.EDITOR_NAME), 'resource.steps', []))

  return (
    <EditorPage
      title={trans('Scenario')}
    >
      <TreeData
        definition={[

        ]}
        data={steps}
        card={StepCard}
        actions={(rows) => [

        ]}
      />
      {/*<ContentSummary
        className={props.className}
        links={steps.map(getStepSummary)}
        noCollapse={true}
      />*/}
    </EditorPage>
  )
}

export {
  EditorScenario
}
