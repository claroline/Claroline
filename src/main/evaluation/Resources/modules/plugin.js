/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Evaluation plugin.
 */
registry.add('ClarolineEvaluationBundle', {
  /**
   * Provides Desktop and/or Workspace tools.
   */
  tools: {
    'evaluation': () => { return import(/* webpackChunkName: "evaluation-tool-evaluation" */ '#/main/evaluation/tools/evaluation') }
  },

  data: {
    sources: {
      'resource_attempts'    : () => { return import(/* webpackChunkName: "evaluation-source-resource_attempts" */     '#/main/evaluation/data/sources/resource-attempts') },
      'resource_evaluations' : () => { return import(/* webpackChunkName: "evaluation-source-resource_evaluations" */  '#/main/evaluation/data/sources/resource-evaluations') },
      'my_resource_evaluations' : () => { return import(/* webpackChunkName: "evaluation-source-my_resource_evaluations" */  '#/main/evaluation/data/sources/my-resource-evaluations') },
      'workspace_evaluations': () => { return import(/* webpackChunkName: "evaluation-source-workspace_evaluations" */ '#/main/evaluation/data/sources/workspace-evaluations') },
      'my_workspace_evaluations': () => { return import(/* webpackChunkName: "evaluation-source-my_workspace_evaluations" */ '#/main/evaluation/data/sources/my-workspace-evaluations') }
    }
  },

  analytics: {
    resource: {
      'evaluation': () => { return import(/* webpackChunkName: "evaluation-analytics-resource-evaluation" */ '#/main/evaluation/analytics/resource/evaluation') }
    }
  }
})
