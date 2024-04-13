import 'dart:convert';
import 'package:http/http.dart' as http;
// import 'dart:io';
// import 'dart:math';

void main() async {

  final apiUrl = 'http://localhost:3000/Final APIs/login-api.php';
  
  // Create a JSON object to send in the request body
  final data = {'field1': 'value1'};

  // Encode the data as JSON
  final jsonData = json.encode(data);

  // Set up the request headers
  final headers = {
    'Content-Type': 'application/json',
    'Authorization': 'Password',
  };

  // Send the request
  final response = await http.post(
    Uri.parse(apiUrl),
    headers: headers,
    body: jsonData,
  );

  if (response.statusCode != 200) {
    print("The error is:");
    print(response.body);
    return;
  }
  // Print the response
  print(response.body);

}
