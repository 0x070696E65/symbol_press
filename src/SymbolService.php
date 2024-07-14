<?php
namespace SymbolPress;

use SymbolSdk\CryptoTypes\PrivateKey;
use SymbolSdk\CryptoTypes\PublicKey;
use SymbolSdk\Symbol\KeyPair;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey as ModelsPublicKey;
use SymbolSdk\Symbol\Models\Timestamp;
use SymbolSdk\Symbol\Models\Transaction;
use SymbolSdk\Facade\SymbolFacade;
use SymbolSdk\Symbol\IdGenerator;
use SymbolRestClient\Api\TransactionRoutesApi;
use SymbolRestClient\Configuration;

use GuzzleHttp\Client;
use Exception;
use SymbolSdk\Symbol\Models\UnresolvedAddress;
use SymbolSdk\Utils\Converter;

class SymbolService{
  const TEST_NET_EXPLORER = 'https://testnet.symbol.fyi';
  const MAIN_NET_EXPLORER = 'https://symbol.fyi';

  private TransactionRoutesApi $transactionRoutesApi;
  private Client $client;
  private string $networkType;
  public SymbolFacade $facade;
  private int $feeMultiplier;
  private int $deadLineSeconds;
  private string $exploer;

  public function __construct(string $node, int $feeMultiplier = 100, int $deadLineSeconds = 3600)
  {
    $config = new Configuration();
    $config->setHost($node);
    $this->client = new Client();
    $this->transactionRoutesApi = new TransactionRoutesApi($this->client, $config);
    $nodeHealth = self::getRequest($node . "/node/health");
    if($nodeHealth['status']['apiNode'] != "up" || $nodeHealth['status']['db'] != "up" )
      throw new Exception('node is not good health');
    $nodeInfo = $this->getRequest($node . "/node/info");
    if($nodeInfo['networkIdentifier'] == 152) {
      $this->networkType = 'testnet';
      $this->exploer = self::TEST_NET_EXPLORER;
    } else {
      $this->networkType = 'mainnet';
      $this->exploer = self::MAIN_NET_EXPLORER;
    }
    $this->facade = new SymbolFacade($this->networkType);
    $this->feeMultiplier = $feeMultiplier;
    $this->deadLineSeconds = $deadLineSeconds;
  }

  public function createTransactionHeader(Transaction &$transaction, $arrgs){
    //$account = $this->facade->createAccount(new PrivateKey($arrgs['private_key']));
    if($arrgs['signer_public_key'] != 'self') {
      $transaction->signerPublicKey = new ModelsPublicKey($arrgs['signer_public_key']);//$account->publicKey;
    }
    $transaction->deadline = new Timestamp($this->facade->now()->addSeconds($this->deadLineSeconds)->timestamp);
    $transaction->network = new NetworkType($this->networkType == 'testnet' ? NetworkType::TESTNET : NetworkType::MAINNET);
  }

  public function signTransaction(Transaction &$transaction, PrivateKey $privateKey){
    $keyPair = new KeyPair($privateKey);
    $signature = $this->facade->signTransaction($keyPair, $transaction);
    return [
      "payload" => $this->facade->attachSignature($transaction, $signature),
      "hash" => strtoupper(bin2hex($this->facade->hashTransaction($transaction)->binaryData))
    ];
  }

  public function getPayload(Transaction &$transaction){
    $hexPayload = strtoupper(bin2hex($transaction->serialize()));
    return [
      "payload" => ['payload' => $hexPayload],
      "hash" => strtoupper(bin2hex($this->facade->hashTransaction($transaction)->binaryData))
    ];
  }

  public function setFee(&$transaction, $cosignatureCount = 0){
    $this->facade->setMaxFee($transaction, $this->feeMultiplier, $cosignatureCount);
  }

  public function getRequest($url) {
    try {
      $response = $this->client->request('GET', $url);
      if ($response->getStatusCode() == 200) {
        $body = $response->getBody();
        $data = json_decode($body, true);
        return $data;
      } else {
        throw new Exception('Request failed with status code: ' . $response->getStatusCode());
      }
    } catch (Exception $e) {
      echo 'Error: ' . $e->getMessage();
    }
  }

  public function accounceTransaction($signedTransaction){
    try {
      $this->transactionRoutesApi->announceTransaction($signedTransaction['payload']);
      $signedTransactionHash = $signedTransaction["hash"];
      $explorerLink = "<a href='{$this->exploer}/transactions/{$signedTransactionHash}' target='_blank'>To Explorer</a>";
      return [
        "isSuccess" => true,
        "message" => $explorerLink
      ];
    } catch (Exception $e) {
      return [
        "isSuccess" => false,
        "message" => $e->getMessage()
      ];
    }
  }

  public function generateMosaicId(string $publicKeyHex){
    $address = $this->facade->network->publicKeyToAddress(new PublicKey($publicKeyHex));
    $mid = IdGenerator::generateMosaicId(new UnresolvedAddress($address->binaryData));
    return [
      "id" => Converter::intToHex($mid['id'], 8, true),
      "nonce" => $mid['nonce']
    ];
  }
}