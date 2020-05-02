Feature:
  In order to get actionable insights about posts
  As an analyst
  I want to be able to calculate max post lengths by month

  Scenario: User successfully generates max post lengths
    Given there are posts loaded from file '/tests/fixtures/posts.json'
    When I generate max post lengths report from 3 post pages
    Then I should get the following stats calculated:
    """
    {
      "max_post_lengths": {
        "2020-Apr": 633,
        "2020-Mar": 510
      }
    }
    """