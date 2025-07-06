class Payment {
  final String id;
  final String referenceID;
  final DateTime expire;
  final int total;
  final String? paymentCode;
  final String? bank;
  final String? store;
  final String? qrCode;

  Payment({required this.id, required this.referenceID, required this.expire, required this.total, this.paymentCode, this.bank, this.store, this.qrCode});
}
