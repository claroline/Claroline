---
layout: default
title: Release process
---

# Release process

Claroline releases follow the [semantic versioning](https://semver.org/) strategy, and they are published through a *time-based model*:

- A new **Claroline patch version** (e.g. 13.0.46, 13.1.1) comes out *every week* (on Wednesday). We may release additional patch versions if we need to fix production blocking bugs. It only contains bug fixes, so you can safely upgrade your applications;
- A new **Claroline minor version** (e.g. 13.1, 13.2) comes out *every month*. It contains bug fixes and new features, but it doesn't include any breaking change, so you can safely upgrade your applications;
- A new **Claroline major version** (e.g. 13.0, 1400) comes out roughly *every year*. It can contain breaking changes, so you may need to do some changes in your applications before upgrading.
