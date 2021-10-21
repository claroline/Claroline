---
layout: default
title: Contributing
---

# Contributing

Guidelines and good practices for a healthy monolithic repository


## Code changes and commits
1. Follow at least in a reasonable degree Symfony's best practices for Backend and Angular's for Frontend.
2. Any changes or additions to code will be made in new branches. It is up to the developer to decide the name of the branch, preferably in a way that describes the implemented change/addition. Once the branch is merged with master the branch SHOULD BE REMOVED.
3. Write tests and run them before committing any new functionalities or fixes.
4. Run tests on old functionalities to verify they still work after new implementations/changes (one fix does not result in other problems).
5. Even if tests report no problems, test at least the minimum of modifications and functionnalities manually on browser to verify that everything works fine.
6. Once the code committed, it is advisable to have it tested by at least 2 different team members. These 2 members will be designed among all teams every Wednesday during the team meeting.
7. If the code has been verified by the above 2 members then a pull request is created and merged with master. DON'T FORGET to DELETE the branch after it is merged if there are no additional changes to be made.
8. However, in the case of a critical change or refactor in the code, even if the code has been verified by these 2 members it will not be ready for merge unless a test in a production environment has been made. This test is a key step in the process because it verifies the system still works on real use conditions.
9. If the change refers to UX it is a good practice to have it tested by someone experienced in usability and ergonomics. This person can verify if the change made conforms to the general UX frame and will probably not cause greater user confusion (than the one it tries to resolve).
10. If a member discovers a bug in code written by another member he/she is welcome to fix the bug and create a PR. If he/she needs any help or advice he/she is also welcome to contact the member-author of the code.
11. If many commits were made with minor changes it would be best to REBASE the code before pushing. This way it is easier for code reviewers to follow changes and verify code.


## User experience
1. Follow one single list of rules for creating a more uniform UX (avoid the case where every plugin looks and feels like its a completely different system)
2. Create a convention on action and object icons (FontAwesome framework, which icon for what action).
3. Create our own icon sprite for any missing icons.
4. Create a sprite for Resource icons, ideally made from a designer that would follow one single style (flat etc.)
5. Agree on a common action toolbar position and form.
6. Ideally have these rules tested from common/simple users using specific scenarios (create x resource, create a new post in a blog etc.) and monitor their reactions/way of thinking.


## Accessibility
1. The app should satisfy all the Level A and Level AA Success Criteria.
