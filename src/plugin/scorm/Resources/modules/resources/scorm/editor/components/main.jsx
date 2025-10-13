import React from 'react'

import {ResourceEditor} from '#/main/core/resource/editor'

import {ScormEditorAppearance} from '#/plugin/scorm/resources/scorm/editor/components/appearance'
import {ScormEditorOverview} from '#/plugin/scorm/resources/scorm/editor/components/overview'
import {ScormEditorEvaluation} from '#/plugin/scorm/resources/scorm/editor/components/evaluation'

const ScormEditor = () =>
  <ResourceEditor
    overviewPage={ScormEditorOverview}
    appearancePage={ScormEditorAppearance}
    // evaluationPage={ScormEditorEvaluation}
  />

export {
  ScormEditor
}
