import React from 'react'
import {useSelector} from 'react-redux'

import {Alert} from '#/main/app/components/alert'
import {ContentLoader} from '#/main/app/content/components/loader'
import {selectors as toolSelectors, ToolOverview} from '#/main/core/tool'

import {selectors} from '#/main/evaluation/tools/evaluation/store'

const EvaluationOverview = () => {

  const toolLoaded = useSelector(toolSelectors.loaded)
  const workspaceEvaluation = useSelector(selectors.currentWorkspaceEvaluation)

  return (
    <ToolOverview>
      {!toolLoaded &&
        <ContentLoader
          size="lg"
          description="Nous chargeons la progression..."
        />
      }

      {toolLoaded && !workspaceEvaluation &&
        <Alert type="warning">
          Vous n'avez pas de progression pour cet espace.
        </Alert>
      }
    </ToolOverview>
  )
}

export {
  EvaluationOverview
}
