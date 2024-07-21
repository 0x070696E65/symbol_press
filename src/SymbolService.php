<?php
namespace SymbolPress;

include_once(ABSPATH . 'wp-content/plugins/symbol-transactions/admin/admin-page.php');

use Error;
use SymbolSdk\CryptoTypes\PrivateKey;
use SymbolSdk\Symbol\KeyPair;
use SymbolSdk\Symbol\Models\NetworkType;
use SymbolSdk\Symbol\Models\PublicKey as ModelsPublicKey;
use SymbolSdk\Symbol\Models\Timestamp;
use SymbolSdk\Symbol\Models\Transaction;
use SymbolSdk\Facade\SymbolFacade;
use SymbolSdk\Symbol\IdGenerator;

use GuzzleHttp\Client;
use Exception;
use SymbolSdk\Symbol\Models\UnresolvedAddress;
use SymbolSdk\Utils\Converter;

class SymbolService{
  const TEST_NET_EXPLORER = 'https://testnet.symbol.fyi';
  const MAIN_NET_EXPLORER = 'https://symbol.fyi';

  private Client $client;
  private string $networkType;
  public SymbolFacade $facade;
  public string $node;
  private int $feeMultiplier;
  private int $deadLineSeconds;

  public function __construct()
  {
    $data = get_my_plugin_data();
    if (!$data) throw new Error('data is not set, please set datas on admin page');

    $this->node = esc_html($data->node);
    $nodeHealth = self::getRequest($this->node . "/node/health");
    if($nodeHealth['status']['apiNode'] != "up" || $nodeHealth['status']['db'] != "up" )
      throw new Exception('node is not good health');
    $nodeInfo = $this->getRequest($this->node . "/node/info");
    if($nodeInfo['networkIdentifier'] == 152) {
      $this->networkType = 'testnet';
    } else {
      $this->networkType = 'mainnet';
    }
    $this->facade = new SymbolFacade($this->networkType);
    $this->feeMultiplier = esc_html($data->fee_multi_plier);
    $this->deadLineSeconds = esc_html($data->deadline_seconds);
  }

  public function createTransactionHeader(Transaction &$transaction, $arrgs, $isEmbedded = false){
    if($isEmbedded) {
      $transaction->signerPublicKey = new ModelsPublicKey($arrgs['signer_public_key']);
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
      throw new Exception($e->getMessage());
    }
  }

  public function generateMosaicId(string $address){
    $mid = IdGenerator::generateMosaicId(new UnresolvedAddress($address));
    return [
      "id" => Converter::intToHex($mid['id'], 8, true),
      "nonce" => $mid['nonce']
    ];
  }
}