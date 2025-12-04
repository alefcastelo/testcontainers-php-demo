Feature: Create User Without Onboard
  Scenario: Should POST /users successfully
    When I send "POST" request to "/users" with body:
    """
    {
      "email": "test@example.com",
      "password": "password"
    }
    """
    Then I should receive a status code 201 and a json response equals to:
    """
    {
        "id": 2,
        "email": "test@example.com"
    }
    """
