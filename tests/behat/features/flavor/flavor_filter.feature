Feature: Filtering programs with specific flavor

  In order provide an index of different flavored programs
  As a site owner
  I want to see only programs with my flavor

  Background:
    Given there are programs:
      | name         | flavor     |
      | Invaders     | pocketcode |
      | Simple click | luna       |
      | A new world  | pocketcode |
      | Soon to be   | luna       |
      | Just for fun | luna       |


  Scenario: Get most viewed programs of flavor pocketcode

    When I get the most viewed programs with "pocketcode/api/projects/mostViewed.json"
    Then I should get following programs:
      | name         |
      | Invaders     |
      | A new world  |
      | Simple click |
      | Soon to be   |
      | Just for fun |

  Scenario: Get most viewed programs of flavor luna

    When I get the most viewed programs with "luna/api/projects/mostViewed.json"
    Then I should get following programs:
      | name         |
      | Simple click |
      | Soon to be   |
      | Just for fun |
      | Invaders     |
      | A new world  |

  Scenario: Get most downloaded programs of pocketcode

    When I get the most downloaded programs with "pocketcode/api/projects/mostDownloaded.json"
    Then I should get following programs:
      | name         |
      | Invaders     |
      | A new world  |
      | Simple click |
      | Soon to be   |
      | Just for fun |

  Scenario: Get most downloaded programs of flavor luna

    When I get the most downloaded programs with "luna/api/projects/mostDownloaded.json"
    Then I should get following programs:
      | name         |
      | Simple click |
      | Soon to be   |
      | Just for fun |
      | Invaders     |
      | A new world  |

  Scenario: Get recent programs of flavor pocketcode

    When I get the recent programs with "pocketcode/api/projects/recent.json"
    Then I should get following programs:
      | name         |
      | Invaders     |
      | A new world  |
      | Simple click |
      | Soon to be   |
      | Just for fun |

  Scenario: Get recent programs of flavor luna

    When I get the recent programs with "luna/api/projects/recent.json"
    Then I should get following programs:
      | name         |
      | Simple click |
      | Soon to be   |
      | Just for fun |
      | Invaders     |
      | A new world  |

  Scenario: Get all programs of a user no matter the flavor (pocketcode)

    Given All programs are from the same user
    When I get the user's programs with "pocketcode/api/projects/userPrograms.json"
    Then I should get following programs:
      | name         |
      | Invaders     |
      | Simple click |
      | A new world  |
      | Soon to be   |
      | Just for fun |

  Scenario: Get all programs of a user no matter the flavor (luna)

    Given All programs are from the same user
    When I get the user's programs with "luna/api/projects/userPrograms.json"
    Then I should get following programs:
      | name         |
      | Invaders     |
      | Simple click |
      | A new world  |
      | Soon to be   |
      | Just for fun |

    