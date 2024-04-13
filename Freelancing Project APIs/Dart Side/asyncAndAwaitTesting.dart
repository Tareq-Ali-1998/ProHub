import 'dart:async';


Future<void> printDelayed(String word) async {
  await Future.delayed(Duration(seconds: 5));
  print(word);
}

void main() {
  print('Start');
  printDelayed('Hello').then((_) {
    print('Async operation completed');
  });
  print('End');
}


/*
Future<void> printDelayed(String word) async {
  await Future.delayed(Duration(seconds: 5));
  print(word);
}

void main() async {
  print('Start');
  await printDelayed('Hello');
  print('End');
}

*/